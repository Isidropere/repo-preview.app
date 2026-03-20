/**
 * ChatScreen.tsx — Pantalla de chat entre dos usuarios
 *
 * Muestra la conversación completa con burbujas de mensajes:
 *   - Mis mensajes → burbuja derecha (color primario)
 *   - Sus mensajes → burbuja izquierda (surface con borde)
 *
 * Al abrir el chat, marca como leídos los mensajes recibidos.
 * El input soporta múltiples líneas (máx 100px de alto).
 * KeyboardAvoidingView evita que el teclado tape el input en iOS.
 *
 * CODIFICABLE: agregar envío de imágenes, indicador de "escribiendo",
 * WebSocket para mensajes en tiempo real.
 */
import React, {useEffect, useState, useRef} from 'react';
import {View, Text, FlatList, StyleSheet, TextInput, TouchableOpacity, KeyboardAvoidingView, Platform, Image} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {getMessages, sendMessage} from '../services/messageService';
import Loading from '../../../shared/components/Loading';

const ChatScreen = ({route, navigation}: any) => {
  const {userId, nombre, foto} = route.params;
  const [messages, setMessages] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [text, setText] = useState('');
  const [sending, setSending] = useState(false);
  const listRef = useRef<FlatList>(null);

  useEffect(() => {
    (async () => {
      try {
        const data = await getMessages(userId);
        setMessages(data || []);
      } catch (_e) {}
      setLoading(false);
    })();
  }, [userId]);

  const handleSend = async () => {
    if (!text.trim() || sending) return;
    setSending(true);
    try {
      const msg = await sendMessage(userId, text.trim());
      setMessages(prev => [...prev, msg]);
      setText('');
      setTimeout(() => listRef.current?.scrollToEnd({animated: true}), 100);
    } catch (_e) {}
    setSending(false);
  };

  const formatTime = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleTimeString('es-DO', {hour: '2-digit', minute: '2-digit'});
  };

  return (
    <SafeAreaView style={styles.flex} edges={['top', 'bottom']}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} accessibilityLabel="Volver" style={styles.backBtn}>
          <Icon name="arrow-back" size={24} color={colors.textPrimary} />
        </TouchableOpacity>
        {foto ? (
          <Image source={{uri: foto}} style={styles.avatar} />
        ) : (
          <View style={styles.avatarPlaceholder}>
            <Icon name="person" size={18} color={colors.textSecondary} />
          </View>
        )}
        <Text style={styles.headerName} numberOfLines={1}>{nombre}</Text>
      </View>

      {loading ? <Loading /> : (
        <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined} keyboardVerticalOffset={0}>
          <FlatList
            ref={listRef}
            data={messages}
            keyExtractor={item => String(item.id)}
            contentContainerStyle={styles.msgList}
            onContentSizeChange={() => listRef.current?.scrollToEnd({animated: false})}
            renderItem={({item}) => (
              <View style={[styles.bubble, item.es_mio ? styles.bubbleMine : styles.bubbleOther]}>
                <Text style={[styles.bubbleText, item.es_mio ? styles.bubbleTextMine : styles.bubbleTextOther]}>{item.mensaje}</Text>
                <Text style={styles.bubbleTime}>{formatTime(item.fecha)}</Text>
              </View>
            )}
          />

          <View style={styles.inputRow}>
            <TextInput
              style={styles.input}
              placeholder="Escribe un mensaje..."
              placeholderTextColor={colors.textSecondary}
              value={text}
              onChangeText={setText}
              multiline
              maxLength={1000}
            />
            <TouchableOpacity
              style={[styles.sendBtn, (!text.trim() || sending) && styles.sendBtnDisabled]}
              onPress={handleSend}
              disabled={!text.trim() || sending}
              accessibilityLabel="Enviar mensaje">
              <Icon name="send" size={20} color="#fff" />
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  header: {flexDirection: 'row', alignItems: 'center', padding: spacing.md, borderBottomWidth: 1, borderBottomColor: colors.border, backgroundColor: colors.surface},
  backBtn: {marginRight: spacing.sm},
  avatar: {width: 36, height: 36, borderRadius: 18, marginRight: spacing.sm},
  avatarPlaceholder: {width: 36, height: 36, borderRadius: 18, backgroundColor: colors.border, justifyContent: 'center', alignItems: 'center', marginRight: spacing.sm},
  headerName: {fontSize: fontSize.md, fontWeight: '600', color: colors.textPrimary, flex: 1},
  msgList: {padding: spacing.md, paddingBottom: spacing.lg},
  bubble: {maxWidth: '78%', padding: spacing.sm, borderRadius: borderRadius.md, marginBottom: spacing.sm},
  bubbleMine: {alignSelf: 'flex-end', backgroundColor: colors.primary},
  bubbleOther: {alignSelf: 'flex-start', backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border},
  bubbleText: {fontSize: fontSize.sm, lineHeight: 20},
  bubbleTextMine: {color: '#fff'},
  bubbleTextOther: {color: colors.textPrimary},
  bubbleTime: {fontSize: 10, color: 'rgba(255,255,255,0.7)', alignSelf: 'flex-end', marginTop: 2},
  inputRow: {flexDirection: 'row', alignItems: 'flex-end', padding: spacing.sm, borderTopWidth: 1, borderTopColor: colors.border, backgroundColor: colors.surface},
  input: {flex: 1, borderWidth: 1, borderColor: colors.border, borderRadius: borderRadius.md, paddingHorizontal: spacing.md, paddingVertical: spacing.sm, fontSize: fontSize.sm, maxHeight: 100, color: colors.textPrimary},
  sendBtn: {backgroundColor: colors.primary, borderRadius: 22, width: 44, height: 44, justifyContent: 'center', alignItems: 'center', marginLeft: spacing.xs},
  sendBtnDisabled: {opacity: 0.5},
});

export default ChatScreen;
