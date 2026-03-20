import React from 'react';
import {View, Text, StyleSheet} from 'react-native';
import {colors, spacing, fontSize} from '../../core/config/theme';
import Button from './Button';

interface ErrorViewProps {
  message?: string;
  onRetry?: () => void;
}

const ErrorView: React.FC<ErrorViewProps> = ({message = 'Ocurrió un error', onRetry}) => (
  <View style={styles.container}>
    <Text style={styles.text}>{message}</Text>
    {onRetry && <Button title="Reintentar" onPress={onRetry} />}
  </View>
);

const styles = StyleSheet.create({
  container: {flex: 1, justifyContent: 'center', alignItems: 'center', padding: spacing.xl},
  text: {fontSize: fontSize.md, color: colors.textSecondary, marginBottom: spacing.md, textAlign: 'center'},
});

export default ErrorView;
