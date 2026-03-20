import React, {useState} from 'react';
import {Text, StyleSheet, KeyboardAvoidingView, Platform, ScrollView} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {colors, spacing, fontSize} from '../../../core/config/theme';
import Input from '../../../shared/components/Input';
import Button from '../../../shared/components/Button';
import {useAuthStore} from '../../../core/store/authStore';
import {login} from '../services/authService';
import {isValidEmail} from '../../../core/utils/validators';

const LoginScreen = ({navigation}: any) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const setAuth = useAuthStore(s => s.setAuth);

  const handleLogin = async () => {
    if (!isValidEmail(email)) {
      setError('Ingresa un correo electrónico válido');
      return;
    }
    if (password.length < 6) {
      setError('La contraseña debe tener al menos 6 caracteres');
      return;
    }
    setError('');
    setLoading(true);
    try {
      const {user, token} = await login(email, password);
      await setAuth(user, token);
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al iniciar sesión');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.flex}>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
          <Text style={styles.title} accessibilityRole="header">Cambialord</Text>
          <Text style={styles.subtitle}>Inicia sesión en tu cuenta</Text>
          {error ? <Text style={styles.error} accessibilityRole="alert">{error}</Text> : null}
          <Input label="Correo electrónico" value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" autoComplete="email" />
          <Input label="Contraseña" value={password} onChangeText={setPassword} secureTextEntry autoComplete="password" />
          <Button title="Iniciar Sesión" onPress={handleLogin} loading={loading} disabled={!email || !password} />
          <Button title="Crear cuenta" onPress={() => navigation.navigate('Register')} variant="outline" />
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  container: {flexGrow: 1, justifyContent: 'center', padding: spacing.xl},
  title: {fontSize: fontSize.title, fontWeight: '700', color: colors.primary, textAlign: 'center', marginBottom: spacing.xs},
  subtitle: {fontSize: fontSize.md, color: colors.textSecondary, textAlign: 'center', marginBottom: spacing.xl},
  error: {color: colors.error, textAlign: 'center', marginBottom: spacing.md},
});

export default LoginScreen;
