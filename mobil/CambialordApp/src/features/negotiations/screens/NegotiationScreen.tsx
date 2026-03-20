/**
 * NegotiationScreen.tsx — Pantalla de propuesta de intercambio
 *
 * Permite al comprador enviar una propuesta al vendedor con:
 *   - Mensaje descriptivo (qué ofrece a cambio)
 *   - Monto adicional opcional en RD$
 *
 * Al enviar exitosamente muestra una pantalla de confirmación.
 * Los errores de validación se muestran en rojo sobre el formulario.
 *
 * CODIFICABLE: agregar selección de item propio para intercambio,
 * historial de negociaciones del item.
 */
import React, {useState} from 'react';
import {View, Text, StyleSheet, ScrollView, TextInput} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {colors, spacing, fontSize, borderRadius} from '../../../core/config/theme';
import {createNegotiation} from '../services/negotiationService';
import Button from '../../../shared/components/Button';
import Input from '../../../shared/components/Input';

const NegotiationScreen = ({route, navigation}: any) => {
  const {itemId, vendedor} = route.params || {};
  const [mensaje, setMensaje] = useState('');
  const [monto, setMonto] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async () => {
    if (!mensaje.trim()) {
      setError('Escribe un mensaje para el vendedor');
      return;
    }
    setError('');
    setLoading(true);
    try {
      await createNegotiation(itemId, mensaje.trim(), monto ? parseFloat(monto) : undefined);
      setSuccess(true);
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al enviar la propuesta');
    }
    setLoading(false);
  };

  if (success) {
    return (
      <SafeAreaView style={styles.container} edges={['top']}>
        <View style={styles.successBox}>
          <Text style={styles.successIcon}>✓</Text>
          <Text style={styles.successTitle}>Propuesta enviada</Text>
          <Text style={styles.successMsg}>El vendedor recibirá tu propuesta de intercambio.</Text>
          <Button title="Volver" onPress={() => navigation.goBack()} />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.title} accessibilityRole="header">Proponer Intercambio</Text>
        {vendedor && <Text style={styles.subtitle}>A: {vendedor.nombre}</Text>}

        {error ? <Text style={styles.error} accessibilityRole="alert">{error}</Text> : null}

        <Input
          label="Mensaje al vendedor"
          value={mensaje}
          onChangeText={setMensaje}
          multiline
          numberOfLines={4}
          placeholder="Describe qué ofreces a cambio..."
          style={styles.textArea}
        />

        <Input
          label="Monto adicional (opcional)"
          value={monto}
          onChangeText={setMonto}
          keyboardType="numeric"
          placeholder="RD$ 0.00"
        />

        <Button title="Enviar Propuesta" onPress={handleSubmit} loading={loading} disabled={!mensaje.trim()} />
        <Button title="Cancelar" variant="outline" onPress={() => navigation.goBack()} />
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background},
  content: {padding: spacing.lg},
  title: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.xs},
  subtitle: {fontSize: fontSize.md, color: colors.textSecondary, marginBottom: spacing.lg},
  error: {color: colors.error, textAlign: 'center', marginBottom: spacing.md},
  textArea: {minHeight: 100, textAlignVertical: 'top'},
  successBox: {flex: 1, justifyContent: 'center', alignItems: 'center', padding: spacing.xl},
  successIcon: {fontSize: 64, color: colors.success, marginBottom: spacing.md},
  successTitle: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary, marginBottom: spacing.sm},
  successMsg: {fontSize: fontSize.md, color: colors.textSecondary, textAlign: 'center', marginBottom: spacing.xl},
});

export default NegotiationScreen;
