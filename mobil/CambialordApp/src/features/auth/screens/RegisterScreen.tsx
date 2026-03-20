import React, {useState} from 'react';
import {Text, StyleSheet, KeyboardAvoidingView, Platform, ScrollView} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {colors, spacing, fontSize} from '../../../core/config/theme';
import Input from '../../../shared/components/Input';
import Button from '../../../shared/components/Button';
import {register} from '../services/authService';
import {useAuthStore} from '../../../core/store/authStore';
import {isValidEmail, minLength} from '../../../core/utils/validators';

const RegisterScreen = ({navigation}: any) => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const setAuth = useAuthStore(s => s.setAuth);

  const handleRegister = async () => {
    if (!minLength(name, 2)) {
      setError('El nombre debe tener al menos 2 caracteres');
      return;
    }
    if (!isValidEmail(email)) {
      setError('Ingresa un correo electrónico válido');
      return;
    }
    if (!minLength(password, 8)) {
      setError('La contraseña debe tener al menos 8 caracteres');
      return;
    }
    if (password !== confirmPassword) {
      setError('Las contraseñas no coinciden');
      return;
    }
    setError('');
    setLoading(true);
    try {
      const {user, token} = await register(name, email, password);
      await setAuth(user, token);
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al registrarse');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.flex}>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
          <Text style={styles.title} accessibilityRole="header">Crear Cuenta</Text>
          {error ? <Text style={styles.error} accessibilityRole="alert">{error}</Text> : null}
          <Input label="Nombre completo" value={name} onChangeText={setName} autoComplete="name" />
          <Input label="Correo electrónico" value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" autoComplete="email" />
          <Input label="Contraseña" value={password} onChangeText={setPassword} secureTextEntry autoComplete="new-password" />
          <Input label="Confirmar contraseña" value={confirmPassword} onChangeText={setConfirmPassword} secureTextEntry autoComplete="new-password" />
          <Button title="Registrarse" onPress={handleRegister} loading={loading} disabled={!name || !email || !password || !confirmPassword} />
          <Button title="Ya tengo cuenta" onPress={() => navigation.goBack()} variant="outline" />
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  flex: {flex: 1, backgroundColor: colors.background},
  container: {flexGrow: 1, justifyContent: 'center', padding: spacing.xl},
  title: {fontSize: fontSize.xxl, fontWeight: '700', color: colors.textPrimary, textAlign: 'center', marginBottom: spacing.xl},
  error: {color: colors.error, textAlign: 'center', marginBottom: spacing.md},
});

export default RegisterScreen;
