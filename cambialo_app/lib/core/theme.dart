import 'package:flutter/material.dart';

// ── Colores del proyecto (extraídos del CSS de la web) ──────────────────
const kPrimary   = Color(0xFFF58634); // naranja — color principal
const kSecondary = Color(0xFF479BD5); // azul — botones de acción
const kHoverSecondary = Color(0xFF3A8BC0);
const kBgGray    = Color(0xFFEEEEEE);
const kBgLight   = Color(0xFFFAFAFA);
const kTextDark  = Color(0xFF1F2937); // gray-800
const kTextGray  = Color(0xFF6B7280); // gray-500

ThemeData appTheme() {
  return ThemeData(
    useMaterial3: true,
    fontFamily: 'Roboto',
    colorScheme: ColorScheme.fromSeed(
      seedColor: kPrimary,
      primary: kPrimary,
      secondary: kSecondary,
    ),
    scaffoldBackgroundColor: kBgLight,
    appBarTheme: const AppBarTheme(
      backgroundColor: Colors.white,
      foregroundColor: kTextDark,
      elevation: 1,
      shadowColor: Color(0x1A000000),
      titleTextStyle: TextStyle(
        color: kTextDark,
        fontSize: 18,
        fontWeight: FontWeight.w600,
        fontFamily: 'Roboto',
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: kSecondary,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 14),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      border: UnderlineInputBorder(
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      enabledBorder: UnderlineInputBorder(
        borderSide: BorderSide(color: Colors.grey.shade300, width: 2),
      ),
      focusedBorder: const UnderlineInputBorder(
        borderSide: BorderSide(color: kPrimary, width: 2),
      ),
      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
      contentPadding: const EdgeInsets.symmetric(vertical: 8),
    ),
  );
}
