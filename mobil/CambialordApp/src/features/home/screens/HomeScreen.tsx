/**
 * HomeScreen.tsx — Pantalla principal / catálogo de productos
 *
 * Muestra el grid de productos con:
 *   - Barra de búsqueda por nombre
 *   - Grid de 2 columnas con imagen, precio y condición
 *   - Infinite scroll (carga más al llegar al final)
 *   - Pull-to-refresh para recargar
 *   - Badge de descuento si el item tiene descuento
 *
 * Al tocar un producto navega a ProductDetail pasando el itemId.
 *
 * CODIFICABLE: agregar filtro por categoría, ordenamiento por precio.
 */
import React, {useEffect, useState, useCallback} from 'react';
import {View, Text, FlatList, StyleSheet, RefreshControl, Image, TouchableOpacity, TextInput} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getProducts} from '../../products/services/productService';
import Card from '../../../shared/components/Card';
import Loading from '../../../shared/components/Loading';
import ErrorView from '../../../shared/components/ErrorView';
import EmptyView from '../../../shared/components/EmptyView';

const PLACEHOLDER_IMG = 'https://via.placeholder.com/200x200.png?text=Sin+Imagen';

const condicionLabel = (c: number) => {
  const map: Record<number, string> = {1: 'Nuevo', 2: 'Usado', 3: 'Reacondicionado', 4: 'Como nuevo'};
  return map[c] || '';
};

const HomeScreen = ({navigation}: any) => {
  const [products, setProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [error, setError] = useState('');

  const fetchData = useCallback(async (p = 1, q = '') => {
    try {
      setError('');
      const res = await getProducts(p, q);
      const items = res.data || res || [];
      if (p === 1) {
        setProducts(items);
      } else {
        setProducts(prev => [...prev, ...items]);
      }
      setHasMore(res.meta ? p < res.meta.last_page : items.length >= 20);
    } catch (_e) {
      setError('Error al cargar productos');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  // Cargar productos al montar el componente
  useEffect(() => { fetchData(); }, [fetchData]);

  // Ejecutar búsqueda al presionar Enter o el botón de lupa
  const handleSearch = () => {
    setPage(1);
    setLoading(true);
    fetchData(1, search);
  };

  // Cargar siguiente página al llegar al 50% del final de la lista
  const loadMore = () => {
    if (!hasMore || loading) return;
    const next = page + 1;
    setPage(next);
    fetchData(next, search);
  };

  if (loading && products.length === 0) return <Loading />;
  if (error && products.length === 0) return <ErrorView message={error} onRetry={() => fetchData()} />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Text style={styles.title} accessibilityRole="header">Cambialord</Text>

      <View style={styles.searchRow}>
        <TextInput
          style={styles.searchInput}
          placeholder="Buscar productos..."
          placeholderTextColor={colors.textSecondary}
          value={search}
          onChangeText={setSearch}
          onSubmitEditing={handleSearch}
          returnKeyType="search"
        />
        <TouchableOpacity style={styles.searchBtn} onPress={handleSearch} accessibilityLabel="Buscar">
          <Icon name="search" size={20} color="#fff" />
        </TouchableOpacity>
      </View>

      {products.length === 0 ? (
        <EmptyView message="No hay productos disponibles" />
      ) : (
        <FlatList
          data={products}
          keyExtractor={item => String(item.id)}
          numColumns={2}
          columnWrapperStyle={styles.row}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); setPage(1); fetchData(1, search); }} tintColor={colors.primary} />
          }
          onEndReached={loadMore}
          onEndReachedThreshold={0.5}
          renderItem={({item}) => (
            <TouchableOpacity
              style={styles.productCard}
              onPress={() => navigation.navigate('ProductDetail', {itemId: item.id})}
              accessibilityLabel={`Ver ${item.nombre}`}>
              <Card style={styles.cardInner}>
                <Image
                  source={{uri: item.imagen || PLACEHOLDER_IMG}}
                  style={styles.productImage}
                  resizeMode="cover"
                />
                {item.descuento > 0 && (
                  <View style={styles.discountBadge}>
                    <Text style={styles.discountText}>-RD${item.descuento}</Text>
                  </View>
                )}
                <Text style={styles.productName} numberOfLines={2}>{item.nombre}</Text>
                <Text style={styles.productPrice}>RD$ {Number(item.precio).toLocaleString()}</Text>
                {item.condicion ? <Text style={styles.condicion}>{condicionLabel(item.condicion)}</Text> : null}
              </Card>
            </TouchableOpacity>
          )}
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background, padding: spacing.md},
  title: {fontSize: fontSize.xxl, fontWeight: '700', color: colors.primary, marginBottom: spacing.sm},
  searchRow: {flexDirection: 'row', marginBottom: spacing.md},
  searchInput: {
    flex: 1, borderWidth: 1, borderColor: colors.border, borderRadius: borderRadius.sm,
    paddingHorizontal: spacing.md, paddingVertical: spacing.sm, fontSize: fontSize.sm,
    backgroundColor: colors.surface, color: colors.textPrimary,
  },
  searchBtn: {
    backgroundColor: colors.primary, borderRadius: borderRadius.sm,
    paddingHorizontal: spacing.md, justifyContent: 'center', marginLeft: spacing.xs,
  },
  row: {justifyContent: 'space-between'},
  productCard: {width: '48%', marginBottom: spacing.md},
  cardInner: {padding: 0, overflow: 'hidden'},
  productImage: {width: '100%', height: 140, borderTopLeftRadius: borderRadius.md, borderTopRightRadius: borderRadius.md},
  discountBadge: {
    position: 'absolute', top: 8, right: 8, backgroundColor: colors.error,
    borderRadius: borderRadius.sm, paddingHorizontal: 6, paddingVertical: 2,
  },
  discountText: {color: '#fff', fontSize: fontSize.xs, fontWeight: '700'},
  productName: {fontSize: fontSize.sm, color: colors.textPrimary, marginTop: spacing.sm, marginHorizontal: spacing.sm},
  productPrice: {fontSize: fontSize.md, fontWeight: '700', color: colors.primary, marginHorizontal: spacing.sm, marginTop: spacing.xs},
  condicion: {fontSize: fontSize.xs, color: colors.textSecondary, marginHorizontal: spacing.sm, marginBottom: spacing.sm},
});

export default HomeScreen;
