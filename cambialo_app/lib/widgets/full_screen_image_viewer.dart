import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:ui';

class FullScreenImageViewer extends StatefulWidget {
  final List<String> imageUrls;
  final int initialIndex;

  const FullScreenImageViewer({
    super.key,
    required this.imageUrls,
    required this.initialIndex,
  });

  @override
  State<FullScreenImageViewer> createState() => _FullScreenImageViewerState();
}

class _FullScreenImageViewerState extends State<FullScreenImageViewer> {
  late PageController _pageController;
  late int _currentIndex;
  bool _isZoomed = false;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      body: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Stack(
          children: [
            // Galería deslizable
            PageView.builder(
              controller: _pageController,
              itemCount: widget.imageUrls.length,
              physics: _isZoomed
                  ? const NeverScrollableScrollPhysics()
                  : const BouncingScrollPhysics(),
              onPageChanged: (index) {
                setState(() {
                  _currentIndex = index;
                  _isZoomed = false; // Reset zoom state when page changes
                });
              },
              itemBuilder: (context, index) {
                return Center(
                  child: ZoomableImageSlide(
                    imageUrl: widget.imageUrls[index],
                    onZoomChanged: (zoomed) {
                      // Only update the parent's scroll physics if this zoom change comes from the ACTIVE page.
                      // This avoids layout calculations from background/disposed slides affecting parent physics.
                      if (index == _currentIndex) {
                        if (_isZoomed != zoomed) {
                          setState(() {
                            _isZoomed = zoomed;
                          });
                        }
                      }
                    },
                  ),
                );
              },
            ),
            
            // Controles superiores: Botón cerrar e indicador de página
            Positioned(
              top: MediaQuery.of(context).padding.top + 10,
              left: 10,
              right: 10,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    decoration: const BoxDecoration(
                      color: Colors.black54,
                      shape: BoxShape.circle,
                    ),
                    child: IconButton(
                      icon: const Icon(Icons.close, color: Colors.white, size: 28),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ),
                  if (widget.imageUrls.length > 1)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.black54,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${_currentIndex + 1} / ${widget.imageUrls.length}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  const SizedBox(width: 48), // Balancea el botón de cerrar
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ZoomableImageSlide extends StatefulWidget {
  final String imageUrl;
  final ValueChanged<bool> onZoomChanged;

  const ZoomableImageSlide({
    super.key,
    required this.imageUrl,
    required this.onZoomChanged,
  });

  @override
  State<ZoomableImageSlide> createState() => _ZoomableImageSlideState();
}

class _ZoomableImageSlideState extends State<ZoomableImageSlide> {
  final TransformationController _transformationController = TransformationController();
  bool _panEnabled = false;

  @override
  void initState() {
    super.initState();
    _transformationController.addListener(_handleTransformationChanged);
  }

  @override
  void dispose() {
    _transformationController.removeListener(_handleTransformationChanged);
    _transformationController.dispose();
    super.dispose();
  }

  void _handleTransformationChanged() {
    final double scale = _transformationController.value.getMaxScaleOnAxis();
    final bool zoomed = scale > 1.01;
    if (zoomed != _panEnabled) {
      setState(() {
        _panEnabled = zoomed;
      });
      widget.onZoomChanged(zoomed);
    }
  }

  @override
  Widget build(BuildContext context) {
    return InteractiveViewer(
      transformationController: _transformationController,
      minScale: 1.0,
      maxScale: 4.0,
      panEnabled: _panEnabled,
      scaleEnabled: true,
      clipBehavior: Clip.none,
      onInteractionEnd: (details) {
        final double scale = _transformationController.value.getMaxScaleOnAxis();
        if (scale < 1.01) {
          _transformationController.value = Matrix4.identity();
          if (_panEnabled) {
            setState(() {
              _panEnabled = false;
            });
            widget.onZoomChanged(false);
          }
        }
      },
      child: CachedNetworkImage(
        imageUrl: widget.imageUrl,
        fit: BoxFit.contain,
        placeholder: (context, url) => const Center(
          child: CircularProgressIndicator(color: Colors.white),
        ),
        errorWidget: (context, url, error) => const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.broken_image, color: Colors.white54, size: 64),
              SizedBox(height: 8),
              Text(
                'Error al cargar la imagen',
                style: TextStyle(color: Colors.white70, fontSize: 14),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
