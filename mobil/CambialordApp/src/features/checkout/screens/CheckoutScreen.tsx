/**
 * CheckoutScreen.tsx — Pantalla de resumen y confirmación de pedido
 *
 * Muestra el resumen antes de confirmar la compra:
 *   - Dirección de envío predeterminada del usuario
 *   - Lista de items seleccionados del carrito
 *   - Resumen de precios (subtotal, descuento, total)
 *   - Botón "Confirmar Pedido"
 *
 * Carga en paralelo el carrito y el perfil del usuario
 * usando Promise.all para optimizar el tiempo de carga.
 *
 * CODIFICABLE: integrar pasarela de pago (Stripe/Cardnet),
 * seleccionar dirección de envío, agregar cupón de descuento.
 */
import React, {useEffect, useState} from 'react';
import {View, Text, StyleSheet, ScrollView, FlatList, Image} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getCart} from '../../cart/services/cartService';
import {getProfile} from '../../profile/services/profileService';
import Button from '../../../shared/components/Button';
import Loading from '../../../shared/components/Loading';

const PLACEHOLDER = 'https://via.placeholder.com/80x80.png?text=Item';

const CheckoutScreen = ({navigation}: any) => {
  const [items, setItems] = useState<any[]>([]);
  const [totales, setTotales] = useState({total_articulos: 0, total_descuento: 0, total_estimado: 0});
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const [cartRes, profileRes] = await Promise.all([getCart(), getProfile()]);
        const selected = (cartRes.items || []).filter((i: any) => i.es_seleccionado);
        setItems(selected);
        setTotales(cartRes.totales || {total_articulos: 0, total_descuento: 0, total_estimado: 0});
        setProfile(profileRes);
      } catch (_e) {}
      setLoading(false);
    })();
  }, []);

  if (loading) return <Loading />;

  const defaultAddr = profile?.direcciones?.find((d: any) => d.es_predeterminada);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView>
        <View style={styles.header}>
          <Icon name="arrow-back" size={24} color={colors.textPrimary} onPress={() => navigation.goBack()} />
          <Text style={styles.title}>Checkout</Text>
        </View>

        {/* Dirección */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Dirección de envío</Text>
          {defaultAddr ? (
            <View style={styles.addressCard}>
              <Icon name="location-outline" size={20} color={colors.primary} />
              <Text style={styles.addressText}>{defaultAddr.calle} {defaultAddr.numero}, {defaultAddr.municipio}</Text>
            </View>
          ) : (
            <Text style={styles.noAddress}>No tienes dirección registrada</Text>
          )}
        </View>

        {/* Items */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Artículos ({items.length})</Text>
          {items.map((item: any) => (
            <View key={item.id_intencion} style={styles.itemRow}>
              <Image source={{uri: item.imagen || PLACEHOLDER}} style={styles.itemImg} />
              <View style={styles.itemInfo}>
                <Text style={styles.itemName} numberOfLines={1}>{item.nombre}</Text>
                <Text style={styles.itemQty}>Cant: {item.cantidad}</Text>
              </View>
              <Text style={styles.itemPrice}>RD$ {Number(item.precio * item.cantidad).toLocaleString()}</Text>
            </View>
          ))}
        </View>

        {/* Resumen */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Resumen</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>RD$ {Number(totales.total_articulos).toLocaleString()}</Text>
          </View>
          {totales.total_descuento > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Descuento</Text>
              <Text style={[styles.summaryValue, {color: colors.success}]}>-RD$ {Number(totales.total_descuento).toLocaleString()}</Text>
            </View>
          )}
          <View style={[styles.summaryRow, styles.totalRow]}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>RD$ {Number(totales.total_estimado).toLocaleString()}</Text>
          </View>
        </View>

        <View style={styles.paySection}>
          <Button title="Confirmar Pedido" onPress={() => {/* TODO: integrar pago */}} />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background},
  header: {flexDirection: 'row', alignItems: 'center', padding: spacing.md},
  title: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginLeft: spacing.sm},
  section: {marginHorizontal: spacing.md, marginBottom: spacing.lg, backgroundColor: colors.surface, borderRadius: borderRadius.md, padding: spacing.md, elevation: 1},
  sectionTitle: {fontSize: fontSize.md, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.sm},
  addressCard: {flexDirection: 'row', alignItems: 'center'},
  addressText: {fontSize: fontSize.sm, color: colors.textPrimary, marginLeft: spacing.sm, flex: 1},
  noAddress: {fontSize: fontSize.sm, color: colors.textSecondary, fontStyle: 'italic'},
  itemRow: {flexDirection: 'row', alignItems: 'center', paddingVertical: spacing.xs, borderBottomWidth: 1, borderBottomColor: colors.border},
  itemImg: {width: 50, height: 50, borderRadius: borderRadius.sm},
  itemInfo: {flex: 1, marginLeft: spacing.sm},
  itemName: {fontSize: fontSize.sm, color: colors.textPrimary},
  itemQty: {fontSize: fontSize.xs, color: colors.textSecondary},
  itemPrice: {fontSize: fontSize.sm, fontWeight: '600', color: colors.textPrimary},
  summaryRow: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.xs},
  summaryLabel: {fontSize: fontSize.sm, color: colors.textSecondary},
  summaryValue: {fontSize: fontSize.sm, color: colors.textPrimary},
  totalRow: {borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.sm, marginTop: spacing.xs},
  totalLabel: {fontSize: fontSize.lg, fontWeight: '700', color: colors.textPrimary},
  totalValue: {fontSize: fontSize.lg, fontWeight: '700', color: colors.primary},
  paySection: {padding: spacing.md},
});

export default CheckoutScreen;
