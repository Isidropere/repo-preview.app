import 'dart:async';
import 'package:flutter/material.dart';

class TickerBannerWidget extends StatefulWidget {
  const TickerBannerWidget({super.key});

  @override
  State<TickerBannerWidget> createState() => _TickerBannerWidgetState();
}

class _TickerBannerWidgetState extends State<TickerBannerWidget> {
  late final ScrollController _scrollController;
  Timer? _timer;
  final List<String> _items = [
    '⭐ Publica tus talentos',
    '💡 Si no puedes venderlo ¡Cámbialo!',
    '✨ Encuentra lo que deseas cambiar',
    '🔄 Intercambia tus productos',
    '🛒 Compra lo que necesitas',
  ];

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    WidgetsBinding.instance.addPostFrameCallback((_) => _startScrolling());
  }

  void _startScrolling() {
    if (!mounted) return;
    _timer = Timer.periodic(const Duration(milliseconds: 30), (timer) {
      if (!mounted || !_scrollController.hasClients) return;
      
      final maxExtent = _scrollController.position.maxScrollExtent;
      final currentOffset = _scrollController.offset;
      
      if (currentOffset >= maxExtent) {
        _scrollController.jumpTo(0.0);
      } else {
        _scrollController.jumpTo(currentOffset + 0.8);
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Repetir la lista de elementos para lograr un scroll continuo fluido e infinito
    final repeatedItems = [..._items, ..._items, ..._items, ..._items];

    return Container(
      height: 32,
      color: const Color(0xFF3498DB),
      child: Center(
        child: ListView.builder(
          controller: _scrollController,
          scrollDirection: Axis.horizontal,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: repeatedItems.length,
          itemBuilder: (context, index) {
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Center(
                child: Text(
                  repeatedItems[index],
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
