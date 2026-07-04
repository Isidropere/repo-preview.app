import 'package:flutter/material.dart';
import '../core/api_client.dart';

class ItemImage extends StatelessWidget {
  final Map item;
  final String? imageUrl;
  final double? width;
  final double? height;
  final BoxFit fit;

  const ItemImage({
    super.key,
    required this.item,
    this.imageUrl,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
  });

  @override
  Widget build(BuildContext context) {
    final rawUrl = imageUrl ?? item['image_url'] as String?;
    final imgUrl = ApiClient.fixImageUrl(rawUrl);
    
    // Si no tiene imagen original o devuelve un default
    final isPlaceholder = rawUrl == null || 
                          rawUrl.trim().isEmpty || 
                          imgUrl == 'https://via.placeholder.com/150' ||
                          imgUrl.contains('via.placeholder.com') ||
                          imgUrl.contains('producto_default') ||
                          imgUrl.contains('producto_defaul') ||
                          imgUrl.contains('servicio_default') ||
                          imgUrl.contains('.svg') ||
                          imgUrl.contains('no-product') ||
                          imgUrl.contains('default-avatar');
    
    if (!isPlaceholder) {
      return Image.network(
        imgUrl,
        width: width,
        height: height,
        fit: fit,
        errorBuilder: (_, __, ___) => _buildFallback(),
      );
    }
    return _buildFallback();
  }

  Widget _buildFallback() {
    // Si id_categoria_item == 29 o id_tipo_item == 2, es Talento/Servicio
    final isService = item['id_categoria_item'] == 29 || item['id_tipo_item'] == 2;
    final tienePendientes = item['tiene_imagenes_pendientes'] == true;

    return Container(
      width: width,
      height: height,
      color: tienePendientes 
          ? Colors.grey.shade100 
          : (isService ? Colors.blue.shade50 : Colors.green.shade50),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            tienePendientes 
                ? Icons.hourglass_empty_outlined 
                : (isService ? Icons.handyman_outlined : Icons.inventory_2_outlined),
            color: tienePendientes 
                ? Colors.grey.shade400 
                : (isService ? Colors.blue.shade300 : Colors.green.shade300),
            size: (height != null && height! < 60) ? 24 : 40,
          ),
          if (height == null || height! >= 60) ...[
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Text(
                tienePendientes 
                    ? 'Imagen en espera de aprobación' 
                    : (isService ? 'Servicio sin foto' : 'Producto sin foto'),
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: (height != null && height! < 100) ? 10 : 12,
                  fontWeight: FontWeight.w500,
                  color: tienePendientes 
                      ? Colors.grey.shade600 
                      : (isService ? Colors.blue.shade400 : Colors.green.shade400),
                ),
              ),
            ),
          ]
        ],
      ),
    );
  }
}
