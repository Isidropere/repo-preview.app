import React from 'react';
import {View, Text, StyleSheet} from 'react-native';
import {colors, spacing, fontSize} from '../../../core/config/theme';

const ProductListScreen = () => (
  <View style={styles.container}>
    <Text style={styles.title}>Productos</Text>
  </View>
);

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: colors.background, padding: spacing.md},
  title: {fontSize: fontSize.xl, fontWeight: '700', color: colors.textPrimary},
});

export default ProductListScreen;
