import React from 'react';
import {View, Text, StyleSheet} from 'react-native';
import {colors, spacing, fontSize} from '../../core/config/theme';

interface EmptyViewProps {
  message?: string;
}

const EmptyView: React.FC<EmptyViewProps> = ({message = 'No hay datos disponibles'}) => (
  <View style={styles.container}>
    <Text style={styles.text}>{message}</Text>
  </View>
);

const styles = StyleSheet.create({
  container: {flex: 1, justifyContent: 'center', alignItems: 'center', padding: spacing.xl},
  text: {fontSize: fontSize.md, color: colors.textSecondary, textAlign: 'center'},
});

export default EmptyView;
