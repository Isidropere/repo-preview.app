/**
 * ConversationListScreen.tsx — Lista de conversaciones
 *
 * Muestra todas las conversaciones del usuario con:
 *   - Avatar del otro usuario
 *   - Último mensaje y tiempo relativo (Ahora / 5m / 2h / 15 ene)
 *   - Badge naranja con cantidad de mensajes no leídos
 *
 * Al tocar una conversación navega a ChatScreen pasando
 * userId, nombre y foto del otro usuario.
 *
 * CODIFICABLE: agregar búsqueda de conversaciones,
 * eliminar conversación, WebSocket para actualizaciones en tiempo real.
 */
import React, {useEffect, useState, useCallback} from 'react';
import {View, Text, FlatList, StyleSheet, Image, TouchableOpacity, RefreshControl} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getConversations} from '../services/messageService';
import Loading from '../../../shared/components/Loading';
import EmptyView from '../../../shared/components/EmptyView';

const ConversationListScreen = ({navigation}: any) => {
  const [conversations, setConversations] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchData = useCallback(async () => {
    try {
      const data = await getConversations();
      setConversations(data || []);
    } catch (_e) {}
    setLoading(false);
    setRefreshing(false);
  }, []);

  useEffect(() => { fetchData(); }, [fetchData]);

  const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    if (diff < 60000) return 'Ahora';
    if (diff < 3600000) return `${Math.floor(diff / 60000)}m`;
    if (diff < 86400000) return `${Math.floor(diff / 3600000)}h`;
    return d.toLocaleDateString('es-DO', {day: 'numeric', month: 'short'});
  };

  if (loading) return <Loading />;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <Text style={styles.title} accessibilityRole="header">Mensajes</Text>

      {conversations.length === 0 ? (
        <EmptyView message="No tienes conversaciones" />
      ) : (
        <FlatList
          data={conversations}
          keyExtractor={item => String(item.user_id)}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchData(); }} tintColor={colors.primary} />}
          renderItem={({item}) => (
            <TouchableOpacity
              style={styles.convRow}
              onPress={() => navigation.navigate('Chat', {userId: item.user_id, nombre: item.nombre, foto: item.foto})}
              accessibilityLabel={`Conversación con ${item.nombre}`}>
              {item.foto ? (
                <Image source={{uri: item.foto}} style={styles.avatar} />
              ) : (
                <View style={styles.avatarPlaceholder}>
                  <Icon name="person" size={22} color={colors.textSecondary} />
                </View>
              )}
              <View style={styles.convInfo}>
                <View style={styles.convHeader}>
                  <Text style={styles.convName} numberOfLines={1}>{item.nombre}</Text>
                  <Text style={styles.convDate}>{formatDate(item.fecha)}</Text>
                </View>
                <View style={styles.convBottom}>
                  <Text style={styles.convMsg} numberOfLines={1}>{item.ultimo_mensaje}</Text>
                  {item.no_leidos > 0 && (
                    <View style={styles.badge}>
                      <Text style={styles.badgeText}>{item.no_leidos}</Text>
                    </View>
                  )}
                </View>
              </View>
            </TouchableOpacity>
          )}
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background, padding: spacing.md},
  title: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.md},
  convRow: {flexDirection: 'row', alignItems: 'center', paddingVertical: spacing.sm, borderBottomWidth: 1, borderBottomColor: colors.border},
  avatar: {width: 50, height: 50, borderRadius: 25},
  avatarPlaceholder: {width: 50, height: 50, borderRadius: 25, backgroundColor: colors.border, justifyContent: 'center', alignItems: 'center'},
  convInfo: {flex: 1, marginLeft: spacing.sm},
  convHeader: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center'},
  convName: {fontSize: fontSize.md, fontWeight: '600', color: colors.textPrimary, flex: 1},
  convDate: {fontSize: fontSize.xs, color: colors.textSecondary, marginLeft: spacing.sm},
  convBottom: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 2},
  convMsg: {fontSize: fontSize.sm, color: colors.textSecondary, flex: 1},
  badge: {backgroundColor: colors.primary, borderRadius: 12, minWidth: 22, height: 22, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 6, marginLeft: spacing.xs},
  badgeText: {color: '#fff', fontSize: fontSize.xs, fontWeight: '700'},
});

export default ConversationListScreen;
