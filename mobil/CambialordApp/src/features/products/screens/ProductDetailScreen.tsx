/**
 * ProductDetailScreen.tsx — Detalle completo de un producto
 *
 * Muestra toda la información del item:
 *   - Carrusel de imágenes con indicadores de puntos
 *   - Nombre, precio, badge de descuento
 *   - Tags de condición y tipo de transacción
 *   - Stock disponible
 *   - Descripción completa
 *   - Colores disponibles con círculos de color
 *   - Datos del vendedor con foto
 *   - Dimensiones y peso
 *
 * Acciones en la barra inferior:
 *   - "Agregar al carrito" → si es venta y hay stock
 *   - "Proponer intercambio" → si acepta intercambio
 *
 * CODIFICABLE: agregar botón de favoritos, compartir,
 * ver más productos del vendedor, rating del vendedor.
 */
import React, {useEffect, useState} from 'react';
import {View, Text, StyleSheet, ScrollView, Image, Dimensions, TouchableOpacity, FlatList} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getProductDetail} from '../services/productService';
import {addToCart} from '../../cart/services/cartService';
import Loading from '../../../shared/components/Loading';
import ErrorView from '../../../shared/components/ErrorView';
import Button from '../../../shared/components/Button';

const {width} = Dimensions.get('window');
const PLACEHOLDER = 'https://via.placeholder.com/400x300.png?text=Sin+Imagen';

const condicionLabel = (c: number) => {
  const map: Record<number, string> = {1: 'Nuevo', 2: 'Usado', 3: 'Reacondicionado', 4: 'Como nuevo'};
  return map[c] || '';
};

const tipoTransLabel = (t: number) => {
  const map: Record<number, string> = {1: 'Venta', 2: 'Intercambio', 3: 'Venta + Intercambio'};
  return map[t] || '';
};

const ProductDetailScreen = ({route, navigation}: any) => {
  const {itemId} = route.params;
  const [product, setProduct] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [addingCart, setAddingCart] = useState(false);
  const [activeImg, setActiveImg] = useState(0);

  useEffect(() => {
    (async () => {
      try {
        const data = await getProductDetail(itemId);
        setProduct(data);
      } catch (_e) {
        setError('Error al cargar el producto');
      } finally {
        setLoading(false);
      }
    })();
  }, [itemId]);

  const handleAddToCart = async () => {
    setAddingCart(true);
    try {
      await addToCart(itemId);
    } catch (_e) {}
    setAddingCart(false);
  };

  if (loading) return <Loading />;
  if (error || !product) return <ErrorView message={error || 'Producto no encontrado'} onRetry={() => navigation.goBack()} />;

  const images = product.imagenes?.length > 0
    ? product.imagenes.map((img: any) => img.url)
    : [PLACEHOLDER];

  return (
    <SafeAreaView style={styles.flex} edges={['top']}>
      <ScrollView style={styles.flex}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} accessibilityLabel="Volver" style={styles.backBtn}>
            <Icon name="arrow-back" size={24} color={colors.textPrimary} />
          </TouchableOpacity>
        </View>

        {/* Image carousel */}
        <FlatList
          data={images}
          horizontal
          pagingEnabled
          showsHorizontalScrollIndicator={false}
          keyExtractor={(_, i) => String(i)}
          onMomentumScrollEnd={(e) => setActiveImg(Math.round(e.nativeEvent.contentOffset.x / width))}
          renderItem={({item}) => (
            <Image source={{uri: item}} style={styles.image} resizeMode="cover" />
          )}
        />
        {images.length > 1 && (
          <View style={styles.dots}>
            {images.map((_: any, i: number) => (
              <View key={i} style={[styles.dot, i === activeImg && styles.dotActive]} />
            ))}
          </View>
        )}

        <View style={styles.content}>
          <Text style={styles.name}>{product.nombre}</Text>
          <View style={styles.priceRow}>
            <Text style={styles.price}>RD$ {Number(product.precio).toLocaleString()}</Text>
            {product.descuento > 0 && (
              <View style={styles.discountBadge}>
                <Text style={styles.discountText}>-RD${product.descuento}</Text>
              </View>
            )}
          </View>

          <View style={styles.tagsRow}>
            <View style={styles.tag}><Text style={styles.tagText}>{condicionLabel(product.condicion)}</Text></View>
            <View style={styles.tag}><Text style={styles.tagText}>{tipoTransLabel(product.tipo_trans)}</Text></View>
            {product.categoria && <View style={styles.tag}><Text style={styles.tagText}>{product.categoria}</Text></View>}
          </View>

          {product.stock !== undefined && (
            <Text style={styles.stock}>
              {product.stock > 0 ? `${product.stock} disponible(s)` : 'Sin stock'}
            </Text>
          )}

          {product.presentacion ? (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Descripción</Text>
              <Text style={styles.description}>{product.presentacion}</Text>
            </View>
          ) : null}

          {/* Colores */}
          {product.colores?.length > 0 && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Colores disponibles</Text>
              <View style={styles.colorsRow}>
                {product.colores.map((c: any) => (
                  <View key={c.id} style={styles.colorItem}>
                    <View style={[styles.colorCircle, {backgroundColor: c.hex || '#ccc'}]} />
                    <Text style={styles.colorName}>{c.nombre}</Text>
                  </View>
                ))}
              </View>
            </View>
          )}

          {/* Vendedor */}
          {product.vendedor && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Vendedor</Text>
              <View style={styles.sellerRow}>
                {product.vendedor.foto ? (
                  <Image source={{uri: product.vendedor.foto}} style={styles.sellerPhoto} />
                ) : (
                  <View style={styles.sellerPhotoPlaceholder}>
                    <Icon name="person" size={20} color={colors.textSecondary} />
                  </View>
                )}
                <Text style={styles.sellerName}>{product.vendedor.nombre}</Text>
              </View>
            </View>
          )}

          {/* Dimensiones */}
          {(product.peso_lbs > 0 || product.alto_cm > 0) && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>Dimensiones</Text>
              <Text style={styles.dimText}>
                {product.peso_lbs > 0 ? `Peso: ${product.peso_lbs} lbs  ` : ''}
                {product.alto_cm > 0 ? `${product.alto_cm}×${product.ancho_cm}×${product.profundo_cm} cm` : ''}
              </Text>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Bottom actions */}
      <View style={styles.bottomBar}>
        {product.tipo_trans !== 2 && product.stock > 0 && (
          <Button title="Agregar al carrito" onPress={handleAddToCart} loading={addingCart} />
        )}
        {(product.tipo_trans === 2 || product.tipo_trans === 3) && (
          <Button
            title="Proponer intercambio"
            variant="outline"
            onPress={() => navigation.navigate('Negotiation', {itemId: product.id, vendedor: product.vendedor})}
          />
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  header: {position: 'absolute', top: spacing.sm, left: spacing.sm, zIndex: 10},
  backBtn: {backgroundColor: 'rgba(255,255,255,0.9)', borderRadius: 20, padding: 8},
  image: {width, height: 300},
  dots: {flexDirection: 'row', justifyContent: 'center', marginTop: spacing.sm},
  dot: {width: 8, height: 8, borderRadius: 4, backgroundColor: colors.border, marginHorizontal: 3},
  dotActive: {backgroundColor: colors.primary, width: 20},
  content: {padding: spacing.lg},
  name: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.xs},
  priceRow: {flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm},
  price: {fontSize: fontSize.xxl, fontWeight: '700', color: colors.primary},
  discountBadge: {backgroundColor: colors.error, borderRadius: borderRadius.sm, paddingHorizontal: 8, paddingVertical: 2, marginLeft: spacing.sm},
  discountText: {color: '#fff', fontSize: fontSize.xs, fontWeight: '700'},
  tagsRow: {flexDirection: 'row', flexWrap: 'wrap', marginBottom: spacing.md},
  tag: {backgroundColor: colors.border, borderRadius: borderRadius.full, paddingHorizontal: 12, paddingVertical: 4, marginRight: spacing.xs, marginBottom: spacing.xs},
  tagText: {fontSize: fontSize.xs, color: colors.textSecondary},
  stock: {fontSize: fontSize.sm, color: colors.success, marginBottom: spacing.md, fontWeight: '600'},
  section: {marginBottom: spacing.lg},
  sectionTitle: {fontSize: fontSize.md, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.sm},
  description: {fontSize: fontSize.sm, color: colors.textSecondary, lineHeight: 22},
  colorsRow: {flexDirection: 'row', flexWrap: 'wrap'},
  colorItem: {alignItems: 'center', marginRight: spacing.md, marginBottom: spacing.sm},
  colorCircle: {width: 32, height: 32, borderRadius: 16, borderWidth: 1, borderColor: colors.border},
  colorName: {fontSize: fontSize.xs, color: colors.textSecondary, marginTop: 4},
  sellerRow: {flexDirection: 'row', alignItems: 'center'},
  sellerPhoto: {width: 40, height: 40, borderRadius: 20, marginRight: spacing.sm},
  sellerPhotoPlaceholder: {width: 40, height: 40, borderRadius: 20, backgroundColor: colors.border, justifyContent: 'center', alignItems: 'center', marginRight: spacing.sm},
  sellerName: {fontSize: fontSize.md, color: colors.textPrimary, fontWeight: '500'},
  dimText: {fontSize: fontSize.sm, color: colors.textSecondary},
  bottomBar: {padding: spacing.md, borderTopWidth: 1, borderTopColor: colors.border, backgroundColor: colors.surface},
});

export default ProductDetailScreen;
