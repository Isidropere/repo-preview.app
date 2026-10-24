class CarouselSlide {
  final int id;
  final String imagenUrl;
  final String? titulo;
  final String? subtitulo;
  final String? enlace;
  final int orden;
  final bool activo;

  CarouselSlide({
    required this.id,
    required this.imagenUrl,
    this.titulo,
    this.subtitulo,
    this.enlace,
    this.orden = 0,
    this.activo = true,
  });

  factory CarouselSlide.fromJson(Map<String, dynamic> json) {
    return CarouselSlide(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      imagenUrl: json['imagen_url']?.toString() ?? '',
      titulo: json['titulo']?.toString(),
      subtitulo: json['subtitulo']?.toString(),
      enlace: json['enlace']?.toString(),
      orden: json['orden'] is int ? json['orden'] as int : int.tryParse(json['orden']?.toString() ?? '0') ?? 0,
      activo: json['activo'] == true || json['activo'] == 1 || json['activo'] == '1',
    );
  }
}

class CarouselResponse {
  final bool success;
  final bool isAutoplay;
  final int velocidadSegundos;
  final List<CarouselSlide> slides;

  CarouselResponse({
    required this.success,
    required this.isAutoplay,
    required this.velocidadSegundos,
    required this.slides,
  });

  factory CarouselResponse.fromJson(Map<String, dynamic> json) {
    final rawSlides = json['slides'] as List<dynamic>? ?? [];
    return CarouselResponse(
      success: json['success'] == true,
      isAutoplay: json['is_autoplay'] == true,
      velocidadSegundos: json['velocidad_segundos'] is int
          ? json['velocidad_segundos'] as int
          : int.tryParse(json['velocidad_segundos']?.toString() ?? '4') ?? 4,
      slides: rawSlides
          .map((s) => CarouselSlide.fromJson(Map<String, dynamic>.from(s as Map)))
          .toList(),
    );
  }
}
