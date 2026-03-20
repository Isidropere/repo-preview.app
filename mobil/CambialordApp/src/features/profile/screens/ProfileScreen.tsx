/**
 * ProfileScreen.tsx — Pantalla de perfil del usuario
 *
 * Muestra los datos del usuario autenticado:
 *   - Avatar, nombre completo, email y tipo de usuario
 *   - Teléfono y nombre de usuario
 *   - Lista de direcciones registradas (con badge "Principal")
 *   - Botón de cerrar sesión
 *
 * Los datos se cargan desde la API (/api/profile) y se
 * complementan con el store de Zustand como fallback.
 *
 * CODIFICABLE: agregar botón de editar perfil, cambiar foto,
 * gestionar direcciones y ver mis productos.
 */
import React, {useEffect, useState} from 'react';
import {View, Text, StyleSheet, ScrollView, Image} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {useAuthStore} from '../../../core/store/authStore';
import {getProfile} from '../services/profileService';
import Button from '../../../shared/components/Button';
import Loading from '../../../shared/components/Loading';

const ProfileScreen = () => {
  const {user, logout} = useAuthStore();
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const data = await getProfile();
        setProfile(data);
      } catch (_e) {}
      setLoading(false);
    })();
  }, []);

  if (loading) return <Loading />;

  const data = profile || user;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView>
        <Text style={styles.title} accessibilityRole="header">Mi Perfil</Text>

        {/* Avatar */}
        <View style={styles.avatarSection}>
          {data?.foto_perfil ? (
            <Image source={{uri: data.foto_perfil}} style={styles.avatar} />
          ) : (
            <View style={styles.avatarPlaceholder}>
              <Icon name="person" size={48} color={colors.textSecondary} />
            </View>
          )}
          <Text style={styles.name}>{data?.nombres || data?.name || 'Usuario'} {data?.apellidos || ''}</Text>
          <Text style={styles.email}>{data?.email || ''}</Text>
          {data?.tipo_usuario && <Text style={styles.tipo}>{data.tipo_usuario}</Text>}
        </View>

        {/* Info cards */}
        <View style={styles.infoSection}>
          <InfoRow icon="call-outline" label="Teléfono" value={data?.telefono || 'No registrado'} />
          <InfoRow icon="at-outline" label="Usuario" value={data?.nombre_usuario || 'No registrado'} />
        </View>

        {/* Direcciones */}
        {profile?.direcciones?.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Direcciones</Text>
            {profile.direcciones.map((d: any) => (
              <View key={d.id} style={styles.addressCard}>
                <Icon name="location-outline" size={18} color={colors.primary} />
                <View style={styles.addressInfo}>
                  <Text style={styles.addressText}>{d.calle} {d.numero}</Text>
                  <Text style={styles.addressMuni}>{d.municipio || ''}</Text>
                </View>
                {d.es_predeterminada && (
                  <View style={styles.defaultBadge}>
                    <Text style={styles.defaultBadgeText}>Principal</Text>
                  </View>
                )}
              </View>
            ))}
          </View>
        )}

        <View style={styles.logoutSection}>
          <Button title="Cerrar Sesión" onPress={logout} variant="outline" />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const InfoRow = ({icon, label, value}: {icon: string; label: string; value: string}) => (
  <View style={styles.infoRow}>
    <Icon name={icon} size={20} color={colors.primary} />
    <View style={styles.infoContent}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background},
  title: {fontSize: fontSize.xxl, fontWeight: '700', color: colors.textPrimary, padding: spacing.lg, paddingBottom: spacing.sm},
  avatarSection: {alignItems: 'center', paddingVertical: spacing.lg},
  avatar: {width: 100, height: 100, borderRadius: 50, marginBottom: spacing.sm},
  avatarPlaceholder: {width: 100, height: 100, borderRadius: 50, backgroundColor: colors.border, justifyContent: 'center', alignItems: 'center', marginBottom: spacing.sm},
  name: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary},
  email: {fontSize: fontSize.sm, color: colors.textSecondary, marginTop: 2},
  tipo: {fontSize: fontSize.xs, color: colors.primary, fontWeight: '600', marginTop: spacing.xs, backgroundColor: `${colors.primary}15`, paddingHorizontal: 12, paddingVertical: 4, borderRadius: borderRadius.full},
  infoSection: {marginHorizontal: spacing.lg, backgroundColor: colors.surface, borderRadius: borderRadius.md, padding: spacing.md, elevation: 2, shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 4},
  infoRow: {flexDirection: 'row', alignItems: 'center', paddingVertical: spacing.sm, borderBottomWidth: 1, borderBottomColor: colors.border},
  infoContent: {marginLeft: spacing.sm, flex: 1},
  infoLabel: {fontSize: fontSize.xs, color: colors.textSecondary},
  infoValue: {fontSize: fontSize.sm, color: colors.textPrimary, fontWeight: '500'},
  section: {marginHorizontal: spacing.lg, marginTop: spacing.lg},
  sectionTitle: {fontSize: fontSize.md, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.sm},
  addressCard: {flexDirection: 'row', alignItems: 'center', backgroundColor: colors.surface, borderRadius: borderRadius.sm, padding: spacing.sm, marginBottom: spacing.xs},
  addressInfo: {flex: 1, marginLeft: spacing.sm},
  addressText: {fontSize: fontSize.sm, color: colors.textPrimary},
  addressMuni: {fontSize: fontSize.xs, color: colors.textSecondary},
  defaultBadge: {backgroundColor: `${colors.success}20`, borderRadius: borderRadius.full, paddingHorizontal: 8, paddingVertical: 2},
  defaultBadgeText: {fontSize: fontSize.xs, color: colors.success, fontWeight: '600'},
  logoutSection: {padding: spacing.lg, marginTop: spacing.lg},
});

export default ProfileScreen;
