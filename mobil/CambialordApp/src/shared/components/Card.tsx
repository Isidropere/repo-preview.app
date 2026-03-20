import React from 'react';
import {View, StyleSheet, ViewProps} from 'react-native';
import {colors, borderRadius, spacing} from '../../core/config/theme';

const Card: React.FC<ViewProps> = ({children, style, ...props}) => {
  return (
    <View style={[styles.card, style]} {...props}>
      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: borderRadius.md,
    padding: spacing.md,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 3,
  },
});

export default Card;
