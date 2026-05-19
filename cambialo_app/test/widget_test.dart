import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:cambialo_app/screens/editar_perfil_screen.dart';
import 'package:cambialo_app/screens/propuesta_intercambio_screen.dart';
import 'package:cambialo_app/screens/negociacion_detalle_screen.dart';

void main() {
  testWidgets('EditarPerfilScreen UI rendering test', (WidgetTester tester) async {
    final mockUser = {
      'nombres': 'John',
      'apellidos': 'Doe',
      'telefono': '8095551234',
      'nombre_usuario': 'johndoe',
      'profile_photo_url': 'https://i.ibb.co/avatar.png',
    };

    await tester.pumpWidget(MaterialApp(
      home: EditarPerfilScreen(user: mockUser),
    ));

    // Verify fields are populated
    expect(find.text('John'), findsOneWidget);
    expect(find.text('Doe'), findsOneWidget);
    expect(find.text('8095551234'), findsOneWidget);
    expect(find.text('johndoe'), findsOneWidget);

    // Verify Save button exists
    expect(find.text('Guardar cambios'), findsOneWidget);
  });

  testWidgets('PropuestaIntercambioScreen UI rendering test', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(
      home: PropuestaIntercambioScreen(
        receptorItemId: 123,
        nombreArticulo: 'Cámara Canon EOS',
        idCategoriaItem: 15, // Producto
      ),
    ));

    // Check loading indicator shows up initially
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });

  testWidgets('NegociacionDetalleScreen UI rendering test', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(
      home: NegociacionDetalleScreen(
        negociacionId: 456,
      ),
    ));

    // Check loading indicator shows up initially
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });
}
