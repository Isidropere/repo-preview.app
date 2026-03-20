/**
 * CartScreen.tsx — Pantalla del carrito de compras
 *
 * Muestra los items en el carrito con:
 *   - Imagen, nombre y precio de cada item
 *   - Controles de cantidad (+ / -)
 *   - Botón de eliminar item
 *   - Resumen de totales (subtotal, descuento, total)
 *   - Botón "Proceder al pago" → navega a Checkout
 *
 * Cada acción (cambiar cantidad, eliminar) recarga el carrito
 * desde la API para mantener los totales actualizados.
 *
 * CODIFICABLE: agregar swipe-to-delete, checkbox de selección,
 * guardar para después.
 */
import React, {useEffect, useState, useCallback} from 'react';
import {View, Text, FlatList, StyleSheet, Image, TouchableOpacity} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getCart, removeFromCart, updateCantidad} from '../../cart/services/cartService';
import Loading from '../../../shared/components/Loading';
import ErrorView from '../../../shared/components/ErrorView';
import EmptyView from '../../../shared/components/EmptyView';
import Button from '../../../shared/components/Button';

const PLACEHOLDER = 'https://via.placeholder.com/100x100.png?text=Item';

const CartScreen = ({navigation}: any) => {
  const [items, setItems] = useState<any[]>([]);
  const [totales, setTotales] = useState({total_articulos: 0, total_descuento: 0, total_estimado: 0});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const fetchCart = useCallback(async () => {
    try {
      setError('');
      const res = await getCart();
      setItems(res.items || []);
      setTotales(res.totales || {total_articulos: 0, total_descuento: 0, total_estimado: 0});
    } catch (_e) {
      setError('Error al cargar el carrito');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchCart(); }, [fetchCart]);

  const handleRemove = async (itemId: number) => {
    try {
      await removeFromCart(itemId);
      fetchCart();
    } catch (_e) {}
  };

  const handleQty = async (id: number, accion: string) => {
    try {
      await updateCantidad(id, accion);
      fetchCart();
    } catch (_e) {}
  };

  if (loading) return <Loading />;
  if (error) return <ErrorView message={error} onRetry={fetchCart} />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Text style={styles.title} accessibilityRole="header">Mi Carrito</Text>

      {items.length === 0 ? (
        <EmptyView message="Tu carrito está vacío" />
      ) : (
        <>
          <FlatList
            data={items}
            keyExtractor={item => String(item.id_intencion)}
            renderItem={({item}) => (
              <View style={styles.itemRow}>
                <Image source={{uri: item.imagen || PLACEHOLDER}} style={styles.itemImg} />
                <View style={styles.itemInfo}>
                  <Text style={styles.itemName} numberOfLines={2}>{item.nombre}</Text>
                  <Text style={styles.itemPrice}>RD$ {Number(item.precio).toLocaleString()}</Text>
                  <View style={styles.qtyRow}>
                    <TouchableOpacity onPress={() => handleQty(item.id_intencion, 'decrementar')} style={styles.qtyBtn} accessibilityLabel="Reducir cantidad">
                      <Icon name="remove" size={18} color={colors.textPrimary} />
                    </TouchableOpacity>
                    <Text style={styles.qtyText}>{item.cantidad}</Text>
                    <TouchableOpacity onPress={() => handleQty(item.id_intencion, 'incrementar')} style={styles.qtyBtn} accessibilityLabel="Aumentar cantidad">
                      <Icon name="add" size={18} color={colors.textPrimary} />
                    </TouchableOpacity>
                  </View>
                </View>
                <TouchableOpacity onPress={() => handleRemove(item.id_item)} style={styles.removeBtn} accessibilityLabel="Eliminar del carrito">
                  <Icon name="trash-outline" size={20} color={colors.error} />
                </TouchableOpacity>
              </View>
            )}
          />

          <View style={styles.totalesBox}>
            <View style={styles.totalRow}>
              <Text style={styles.totalLabel}>Subtotal</Text>
              <Text style={styles.totalValue}>RD$ {Number(totales.total_articulos).toLocaleString()}</Text>
            </View>
            {totales.total_descuento > 0 && (
              <View style={styles.totalRow}>
                <Text style={styles.totalLabel}>Descuento</Text>
                <Text style={[styles.totalValue, {color: colors.success}]}>-RD$ {Number(totales.total_descuento).toLocaleString()}</Text>
              </View>
            )}
            <View style={[styles.totalRow, styles.totalFinal]}>
              <Text style={styles.totalFinalLabel}>Total</Text>
              <Text style={styles.totalFinalValue}>RD$ {Number(totales.total_estimado).toLocaleString()}</Text>
            </View>
            <Button title="Proceder al pago" onPress={() => navigation.navigate('Checkout')} />
          </View>
        </>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background, padding: spacing.md},
  title: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.md},
  itemRow: {flexDirection: 'row', backgroundColor: colors.surface, borderRadius: borderRadius.md, padding: spacing.sm, marginBottom: spacing.sm, alignItems: 'center', elevation: 2, shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 4},
  itemImg: {width: 80, height: 80, borderRadius: borderRadius.sm},
  itemInfo: {flex: 1, marginLeft: spacing.sm},
  itemName: {fontSize: fontSize.sm, color: colors.textPrimary, fontWeight: '500'},
  itemPrice: {fontSize: fontSize.md, fontWeight: '700', color: colors.primary, marginTop: 2},
  qtyRow: {flexDirection: 'row', alignItems: 'center', marginTop: spacing.xs},
  qtyBtn: {width: 30, height: 30, borderRadius: 15, borderWidth: 1, borderColor: colors.border, justifyContent: 'center', alignItems: 'center'},
  qtyText: {fontSize: fontSize.md, fontWeight: '600', marginHorizontal: spacing.sm, color: colors.textPrimary},
  removeBtn: {padding: spacing.sm},
  totalesBox: {borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.md},
  totalRow: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.xs},
  totalLabel: {fontSize: fontSize.sm, color: colors.textSecondary},
  totalValue: {fontSize: fontSize.sm, color: colors.textPrimary, fontWeight: '500'},
  totalFinal: {borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.sm, marginTop: spacing.xs, marginBottom: spacing.md},
  totalFinalLabel: {fontSize: fontSize.lg, fontWeight: '700', color: colors.textPrimary},
  totalFinalValue: {fontSize: fontSize.lg, fontWeight: '700', color: colors.primary},
});

export default CartScreen;
