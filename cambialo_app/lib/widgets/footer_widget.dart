import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class FooterWidget extends StatelessWidget {
  const FooterWidget({super.key});

  // Método helper para lanzar URLs
  Future<void> _launch(String urlString) async {
    final Uri url = Uri.parse(urlString);
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final baseUrl = kBaseUrl.replaceAll('/api', '');

    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.only(top: 8, bottom: 24, left: 16, right: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Divider(color: Color(0xFFF3F4F6), thickness: 1), // border-gray-100
          const SizedBox(height: 8),

          // Secciones del Footer en Acordeones / ExpansionTiles para diseño móvil limpio
          Theme(
            data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
            child: Column(
              children: [
                _buildExpansionSection(
                  context,
                  title: 'Secciones',
                  items: [
                    _FooterLinkItem(
                      label: 'Sobre Nosotros',
                      onTap: () => _showAboutDialog(context),
                    ),
                    _FooterLinkItem(
                      label: 'Contáctanos',
                      onTap: () => _showContactDialog(context),
                    ),
                    _FooterLinkItem(
                      label: 'Información de envíos',
                      onTap: () => _showShippingDialog(context),
                    ),
                    _FooterLinkItem(
                      label: 'Empleos',
                      onTap: () => _showJobsDialog(context),
                    ),
                  ],
                ),
                const Divider(color: Color(0xFFF3F4F6), height: 1),
                _buildExpansionSection(
                  context,
                  title: 'Ayuda',
                  items: [
                    _FooterLinkItem(
                      label: 'Responsabilidad social',
                      onTap: () => _showSocialResponsibilityDialog(context),
                    ),
                    _FooterLinkItem(
                      label: '¿Cómo realizar un intercambio?',
                      onTap: () => _showHelpPageDialog(context, 'realizar-intercambio'),
                    ),
                    _FooterLinkItem(
                      label: '¿Cómo vender?',
                      onTap: () => _showHelpPageDialog(context, 'como-vender'),
                    ),
                    _FooterLinkItem(
                      label: '¿Cómo realizar una compra?',
                      onTap: () => _showHelpPageDialog(context, 'realizar-compra'),
                    ),
                  ],
                ),
                const Divider(color: Color(0xFFF3F4F6), height: 1),
                _buildExpansionSection(
                  context,
                  title: 'Políticas y Legal',
                  items: [
                    _FooterLinkItem(
                      label: 'Términos y Condiciones',
                      onTap: () => _showPoliticasDialog(context, 0),
                    ),
                    _FooterLinkItem(
                      label: 'Política de Privacidad',
                      onTap: () => _showPoliticasDialog(context, 1),
                    ),
                    _FooterLinkItem(
                      label: 'Devoluciones y Cancelaciones',
                      onTap: () => _showPoliticasDialog(context, 2),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),
          const Divider(color: Color(0xFFF3F4F6), thickness: 1),
          const SizedBox(height: 8),

          // Bloque de información y pagos centralizado
          Center(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const Text(
                  'Cámbialo RD',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: kTextDark,
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Dirección permanente: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 11,
                    color: kTextGray,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 12),

                // Enlaces interactivos Teléfono y Correo
                Wrap(
                  alignment: WrapAlignment.center,
                  spacing: 16,
                  runSpacing: 8,
                  children: [
                    InkWell(
                      onTap: () => _launch('tel:8299634839'),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.phone, size: 14, color: kPrimary),
                          const SizedBox(width: 4),
                          Text(
                            'Soporte: (829) 963-4839',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: kSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                    InkWell(
                      onTap: () => _launch('mailto:cambialord.com@gmail.com'),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.email, size: 14, color: kPrimary),
                          const SizedBox(width: 4),
                          Text(
                            'cambialord.com@gmail.com',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: kSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 24),

                // Tarjetas y pagos aceptados (Requisitos AZUL)
                const Text(
                  'TARJETAS Y PAGOS ACEPTADOS',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.bold,
                    color: kTextGray,
                    letterSpacing: 1.0,
                  ),
                ),
                const SizedBox(height: 12),
                Wrap(
                  alignment: WrapAlignment.center,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  spacing: 16,
                  runSpacing: 12,
                  children: [
                    _buildCardLogo('$baseUrl/imgs/Visa_Brandmark_Blue_RGB_2021.png', height: 20),
                    _buildCardLogo('$baseUrl/imgs/mastercard-logo.png', height: 20),
                    _buildCardLogo('$baseUrl/imgs/visa-secure_blu_2021_dkbg.png', height: 26),
                    _buildCardLogo('$baseUrl/imgs/mastercardidentitycheck.png', height: 26),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFF2563EB), // Azul
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Text(
                        'AZUL',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 1.2,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),
          const Divider(color: Color(0xFFF3F4F6), thickness: 1),
          const SizedBox(height: 6),

          // Copyright y Social Icons
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  '© ${DateTime.now().year} Cámbialo RD.\nTodos los derechos reservados.',
                  style: const TextStyle(
                    fontSize: 10,
                    color: kTextGray,
                    height: 1.3,
                  ),
                ),
              ),
              Row(
                children: [
                  _buildSocialIcon(
                    icon: Icons.camera_alt_outlined, // Instagram replacement
                    url: 'https://www.instagram.com/cambialordo/',
                  ),
                  const SizedBox(width: 4),
                  _buildSocialIcon(
                    icon: Icons.facebook,
                    url: 'https://www.facebook.com/cambialord',
                  ),

                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  // --- WIDGETS AUXILIARES DE DISEÑO ---

  Widget _buildExpansionSection(
    BuildContext context, {
    required String title,
    required List<_FooterLinkItem> items,
  }) {
    return ExpansionTile(
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
          color: kPrimary,
        ),
      ),
      dense: true,
      childrenPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      children: items,
    );
  }

  Widget _buildCardLogo(String url, {required double height}) {
    return Image.network(
      url,
      height: height,
      fit: BoxFit.contain,
      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
    );
  }

  Widget _buildSocialIcon({required IconData icon, required String url}) {
    return IconButton(
      icon: Icon(icon, color: kPrimary, size: 20),
      visualDensity: VisualDensity.compact,
      onPressed: () => _launch(url),
    );
  }

  // --- DIÁLOGOS MODALES ESTÁTICOS ---

  void _showAboutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Sobre Nosotros',
          style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Cámbialo RD nace con la visión de ofrecer una solución innovadora y sostenible en la República Dominicana. Somos una plataforma en línea dedicada a facilitar el intercambio, compra y venta de objetos nuevos o usados en buen estado. Nuestra misión es promover un estilo de vida más ecológico y consciente, brindando a nuestros usuarios la posibilidad de darle una segunda vida a esos artículos que ya no utilizan. Con nuestro eslogan: “Si no puedes venderlo, ¡cámbialo!", queremos incentivar el reciclaje y el ahorro, proporcionando una alternativa práctica para quienes desean obtener nuevos artículos sin necesidad de comprarlos, o simplemente desean vender lo que ya no usan.',
                  style: TextStyle(fontSize: 13, height: 1.4),
                ),
                const SizedBox(height: 16),
                _buildSectionSubTitle('Misión'),
                const Text(
                  'En Cámbialo RD, nuestra misión es transformar la forma en que las personas en la República Dominicana intercambian, compran y venden artículos, promoviendo un consumo consciente y sostenible. Nos dedicamos a ofrecer una plataforma en línea segura y accesible que facilite el aprovechamiento de recursos, reduciendo el desperdicio y fomentando un estilo de vida más ecológico. Buscamos conectar a las personas, dándoles la oportunidad de encontrar nuevas utilidades para los objetos que ya no usan, contribuyendo así a un mundo más responsable y sostenible.',
                  style: TextStyle(fontSize: 13, height: 1.4),
                ),
                const SizedBox(height: 16),
                _buildSectionSubTitle('Visión'),
                const Text(
                  'Nuestra visión es ser la plataforma líder en la República Dominicana para el intercambio, compra y venta de artículos, posicionándonos como un referente en el consumo sostenible y consciente. Aspiramos a expandir nuestra comunidad, creando un impacto positivo tanto en el medio ambiente como en la economía local. Queremos ser reconocidos por nuestra capacidad de conectar a las personas, ofreciendo soluciones innovadoras que faciliten una vida más equilibrada y respetuosa con el entorno.',
                  style: TextStyle(fontSize: 13, height: 1.4),
                ),
                const SizedBox(height: 16),
                _buildSectionSubTitle('Valores'),
                _buildValorItem('Sostenibilidad', 'Fomentamos prácticas que contribuyen a la reducción de desechos y al cuidado del medio ambiente, promoviendo el intercambio y la reutilización de objetos.'),
                _buildValorItem('Responsabilidad', 'Operamos de manera ética y transparente, garantizando que nuestras acciones beneficien a la comunidad y respeten el entorno.'),
                _buildValorItem('Innovación', 'Nos esforzamos por ofrecer soluciones tecnológicas que faciliten la vida de nuestros usuarios, mejorando constantemente nuestra plataforma para adaptarnos a sus necesidades.'),
                _buildValorItem('Confianza', 'Brindamos un entorno seguro y confiable donde nuestros usuarios pueden realizar intercambios, compras y ventas con total tranquilidad.'),
                _buildValorItem('Comunidad', 'Valoramos y fortalecemos las conexiones entre nuestros usuarios, creando un espacio donde todos pueden beneficiarse mutuamente y contribuir al bien común.'),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
          )
        ],
      ),
    );
  }

  void _showContactDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Contáctanos',
          style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              '¿Tienes alguna duda o inconveniente? Ponte en contacto con nosotros a través de cualquiera de nuestros canales oficiales:',
              style: TextStyle(fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.email, color: kPrimary),
              title: const Text('Correo Electrónico', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
              subtitle: const Text('cambialord.com@gmail.com', style: TextStyle(fontSize: 13, color: kSecondary)),
              onTap: () => _launch('mailto:cambialord.com@gmail.com'),
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt, color: kPrimary),
              title: const Text('Instagram', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
              subtitle: const Text('cambialordo', style: TextStyle(fontSize: 13, color: kSecondary)),
              onTap: () => _launch('https://instagram.com/cambialordo'),
            ),
            ListTile(
              leading: const Icon(Icons.facebook, color: kPrimary),
              title: const Text('Facebook', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
              subtitle: const Text('Cámbialo RD', style: TextStyle(fontSize: 13, color: kSecondary)),
              onTap: () => _launch('https://facebook.com/cambialord'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
          )
        ],
      ),
    );
  }

  void _showShippingDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Información de envíos',
          style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.blue.shade100),
                  ),
                  child: Text(
                    'En Cámbialo RD, facilitamos el proceso de intercambio, compra y venta de manera rápida y sencilla. Los envíos de productos se realizan a través de nuestros partners logísticos de confianza.',
                    style: TextStyle(fontSize: 12.5, color: Colors.blue.shade900, height: 1.4),
                  ),
                ),
                const SizedBox(height: 16),
                _buildCardInfo(
                  icon: Icons.swap_horiz,
                  title: 'Intercambios (Trueques)',
                  desc: 'En el caso de intercambios, los usuarios solo deberán cubrir el costo del envío de su respectivo artículo. No hay cargos adicionales por transacción.',
                ),
                const SizedBox(height: 12),
                _buildCardInfo(
                  icon: Icons.shopping_cart_outlined,
                  title: 'Compras directas',
                  desc: 'El cliente pagará tanto el precio del objeto como el costo de envío, el cual será especificado detalladamente al momento de la transacción.',
                ),
                const SizedBox(height: 12),
                _buildCardInfo(
                  icon: Icons.shield_outlined,
                  title: 'Garantía para el Vendedor',
                  desc: 'Si estás vendiendo, recibirás el pago por tu producto una vez que el comprador haya confirmado la recepción de este en perfectas condiciones.',
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
          )
        ],
      ),
    );
  }

  void _showSocialResponsibilityDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Responsabilidad Social',
          style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
        ),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'En Cámbialo RD, la responsabilidad social no es solo un compromiso corporativo; es nuestra razón de ser. Nuestra plataforma fue creada con el firme propósito de mitigar el impacto ambiental y fortalecer la economía familiar en la República Dominicana a través de un modelo de economía circular y consumo consciente.',
                  style: TextStyle(fontSize: 13, height: 1.4),
                ),
                const SizedBox(height: 16),
                _buildResponsibilityCard(
                  title: 'Reducción de Desechos',
                  desc: 'Promovemos la reutilización y el reciclaje práctico de objetos. Al darle una segunda vida a los artículos en buen estado, evitamos que terminen prematuramente en vertederos, disminuyendo la acumulación de residuos en nuestro país.',
                  color: Colors.green.shade50,
                  textColor: Colors.green.shade900,
                  icon: Icons.recycling,
                ),
                const SizedBox(height: 12),
                _buildResponsibilityCard(
                  title: 'Trueque Sostenible',
                  desc: 'Bajo el lema "Si no puedes venderlo, ¡cámbialo!", incentivamos el intercambio directo de bienes sin necesidad de usar dinero, promoviendo el ahorro, la solidaridad y un comercio más inclusivo y humano.',
                  color: Colors.blue.shade50,
                  textColor: Colors.blue.shade900,
                  icon: Icons.sync,
                ),
                const SizedBox(height: 12),
                _buildResponsibilityCard(
                  title: 'Educación Ecológica',
                  desc: 'Operamos de manera transparente y ética, impulsando iniciativas y creando conciencia sobre los beneficios ecológicos de preferir el mercado de segunda mano frente al consumismo masivo tradicional.',
                  color: Colors.orange.shade50,
                  textColor: Colors.orange.shade900,
                  icon: Icons.lightbulb_outline,
                ),
                const SizedBox(height: 12),
                _buildResponsibilityCard(
                  title: 'Unión de Comunidades',
                  desc: 'Facilitamos que personas de diferentes provincias de la República Dominicana conecten de forma segura, construyendo una red colaborativa que promueve la equidad social y el acceso a productos de calidad.',
                  color: Colors.purple.shade50,
                  textColor: Colors.purple.shade900,
                  icon: Icons.people_outline,
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
          )
        ],
      ),
    );
  }

  // --- DIÁLOGOS DINÁMICOS DE API ---

  void _showJobsDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => _DynamicJobsDialog(onLaunch: _launch),
    );
  }

  void _showHelpPageDialog(BuildContext context, String slug) {
    showDialog(
      context: context,
      builder: (ctx) => _DynamicHelpDialog(slug: slug),
    );
  }

  // --- DIÁLOGO DE POLÍTICAS Y LEGAL ---

  void _showPoliticasDialog(BuildContext context, int initialTab) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Políticas y Legal',
          style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
        ),
        content: SizedBox(
          width: double.maxFinite,
          height: 380,
          child: DefaultTabController(
            length: 3,
            initialIndex: initialTab,
            child: Column(
              children: [
                const TabBar(
                  labelColor: kPrimary,
                  unselectedLabelColor: kTextGray,
                  indicatorColor: kPrimary,
                  labelStyle: TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                  tabs: [
                    Tab(text: 'Términos'),
                    Tab(text: 'Privacidad'),
                    Tab(text: 'Devolución'),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      // Términos
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildSectionSubTitle('Términos y Condiciones (Política de Entrega)'),
                            const Text(
                              'Cámbialo RD es un mercado en línea registrado en la República Dominicana.\n\n'
                              '1. Envíos y Entregas: Los artículos se entregan a través de socios logísticos seleccionados. Las entregas se completan entre 2 a 5 días hábiles.\n\n'
                              '2. Restricciones de Envío y Exportación: Los envíos están limitados EXCLUSIVAMENTE al territorio de la República Dominicana. No realizamos exportaciones ni entregas fuera del país.',
                              style: TextStyle(fontSize: 12, height: 1.4, color: kTextGray),
                            ),
                          ],
                        ),
                      ),
                      // Privacidad
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildSectionSubTitle('Política de Privacidad y Seguridad'),
                            const Text(
                              'Protegemos tus datos personales.\n\n'
                              'Seguridad de Tarjetas: No guardamos, almacenamos ni compartimos los números de tus tarjetas de crédito/débito ni el código de seguridad CVV. Toda la transmisión de datos financieros se realiza de forma cifrada mediante protocolo seguro TLS 1.2 directamente a la pasarela de pagos AZUL (Banco Popular Dominicano).',
                              style: TextStyle(fontSize: 12, height: 1.4, color: kTextGray),
                            ),
                          ],
                        ),
                      ),
                      // Devoluciones
                      SingleChildScrollView(
                        padding: const EdgeInsets.only(top: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildSectionSubTitle('Políticas de Devoluciones y Cancelación'),
                            const Text(
                              '1. Devoluciones: Dispones de un plazo de 48 horas contadas a partir de la recepción física del artículo para notificar disconformidades o defectos graves.\n\n'
                              '2. Reembolsos: No se realizan reembolsos de dinero en compras de artículos físicos por cambios de opinión. En caso de devoluciones válidas, los reembolsos se acreditarán a la tarjeta de pago original a través de AZUL.\n\n'
                              '3. Cancelaciones: Los pedidos de productos físicos pueden cancelarse sin cargo antes de ser enviados.',
                              style: TextStyle(fontSize: 12, height: 1.4, color: kTextGray),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
          )
        ],
      ),
    );
  }

  // --- HELPERS INTERNOS DE SUBSECCIONES ---

  Widget _buildSectionSubTitle(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0, top: 4.0),
      child: Text(
        text,
        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: kTextDark),
      ),
    );
  }

  Widget _buildValorItem(String title, String desc) {
    return Padding(
      padding: const EdgeInsets.only(top: 8.0),
      child: RichText(
        text: TextSpan(
          style: const TextStyle(fontSize: 13, color: kTextDark, height: 1.4),
          children: [
            TextSpan(text: '• $title: ', style: const TextStyle(fontWeight: FontWeight.bold)),
            TextSpan(text: desc),
          ],
        ),
      ),
    );
  }

  Widget _buildCardInfo({required IconData icon, required String title, required String desc}) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: kPrimary),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark)),
                const SizedBox(height: 4),
                Text(desc, style: const TextStyle(fontSize: 12, color: kTextGray, height: 1.3)),
              ],
            ),
          )
        ],
      ),
    );
  }

  Widget _buildResponsibilityCard({
    required String title,
    required String desc,
    required Color color,
    required Color textColor,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: textColor.withOpacity(0.2)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: textColor),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: textColor)),
                const SizedBox(height: 4),
                Text(desc, style: TextStyle(fontSize: 12, color: textColor.withOpacity(0.9), height: 1.3)),
              ],
            ),
          )
        ],
      ),
    );
  }
}

// Enlace clickable del footer
class _FooterLinkItem extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _FooterLinkItem({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 6.0),
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 13,
              color: kTextGray,
            ),
          ),
        ),
      ),
    );
  }
}

// --- WIDGETS DE DIÁLOGOS CON PETICIONES DENTRO ---

class _DynamicJobsDialog extends StatefulWidget {
  final Future<void> Function(String) onLaunch;
  const _DynamicJobsDialog({required this.onLaunch});

  @override
  State<_DynamicJobsDialog> createState() => _DynamicJobsDialogState();
}

class _DynamicJobsDialogState extends State<_DynamicJobsDialog> {
  List _jobs = [];
  bool _loading = true;
  bool _error = false;

  @override
  void initState() {
    super.initState();
    _fetchJobs();
  }

  Future<void> _fetchJobs() async {
    try {
      final res = await ApiClient.get('/empleos', useCache: false);
      if (res.statusCode == 200) {
        setState(() {
          _jobs = jsonDecode(res.body);
          _loading = false;
        });
      } else {
        setState(() {
          _error = true;
          _loading = false;
        });
      }
    } catch (_) {
      setState(() {
        _error = true;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Text(
        'Trabaja con Nosotros',
        style: TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
      ),
      content: SizedBox(
        width: double.maxFinite,
        child: _loading
            ? const SizedBox(
                height: 100,
                child: Center(child: CircularProgressIndicator(color: kPrimary)),
              )
            : _error
                ? const SizedBox(
                    height: 100,
                    child: Center(child: Text('Error al cargar ofertas de empleo', style: TextStyle(color: Colors.red))),
                  )
                : SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'En Cámbialo RD estamos en constante crecimiento y buscamos personas apasionadas que deseen formar parte de nuestro equipo. Si compartes nuestra visión de un mundo más sostenible y te entusiasma la idea de transformar la manera en que las personas intercambian y comercian, ¡nos encantaría conocerte!',
                          style: TextStyle(fontSize: 13, height: 1.4),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.orange.shade100),
                          ),
                          child: InkWell(
                            onTap: () => widget.onLaunch('mailto:cambialord.com@gmail.com?subject=Postulacion%20Espontanea'),
                            child: RichText(
                              text: TextSpan(
                                style: TextStyle(fontSize: 12.5, color: Colors.orange.shade900, height: 1.4),
                                children: const [
                                  TextSpan(text: '¿No encuentras una vacante que se ajuste a tu perfil? No te preocupes. Puedes enviarnos tu currículum (CV) en formato PDF directamente a nuestro correo de contacto oficial: '),
                                  TextSpan(
                                    text: 'cambialord.com@gmail.com',
                                    style: TextStyle(fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
                                  ),
                                  TextSpan(text: '. Estaremos encantados de guardarlo para futuras oportunidades.'),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                        const Text(
                          'Vacantes Disponibles',
                          style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: kTextDark),
                        ),
                        const SizedBox(height: 8),
                        if (_jobs.isEmpty)
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(vertical: 24),
                            alignment: Alignment.center,
                            child: Column(
                              children: const [
                                Icon(Icons.work_outline, size: 36, color: Colors.grey),
                                SizedBox(height: 8),
                                Text(
                                  'Actualmente no tenemos vacantes activas. ¡Vuelve pronto!',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(fontSize: 12, color: kTextGray),
                                ),
                              ],
                            ),
                          )
                        else
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _jobs.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 12),
                            itemBuilder: (context, index) {
                              final job = _jobs[index];
                              return Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: kBgLight,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: Colors.grey.shade200),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: kPrimary.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: const Text(
                                        'VACANTE',
                                        style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: kPrimary),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      job['titulo'] ?? '',
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: kTextDark),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      job['descripcion'] ?? '',
                                      style: const TextStyle(fontSize: 12, color: kTextGray, height: 1.3),
                                    ),
                                    if (job['requisitos'] != null && job['requisitos'].toString().isNotEmpty) ...[
                                      const SizedBox(height: 10),
                                      Container(
                                        width: double.infinity,
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            const Text(
                                              'Instrucciones / Requisitos:',
                                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: kPrimary),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              job['requisitos'] ?? '',
                                              style: const TextStyle(fontSize: 11, color: kTextDark, height: 1.3),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ]
                                  ],
                                ),
                              );
                            },
                          ),
                      ],
                    ),
                  ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
        )
      ],
    );
  }
}

class _DynamicHelpDialog extends StatefulWidget {
  final String slug;
  const _DynamicHelpDialog({required this.slug});

  @override
  State<_DynamicHelpDialog> createState() => _DynamicHelpDialogState();
}

class _DynamicHelpDialogState extends State<_DynamicHelpDialog> {
  Map<String, dynamic>? _helpData;
  bool _loading = true;
  bool _error = false;

  @override
  void initState() {
    super.initState();
    _fetchHelp();
  }

  Future<void> _fetchHelp() async {
    try {
      final res = await ApiClient.get('/ayuda/${widget.slug}', useCache: false);
      if (res.statusCode == 200) {
        setState(() {
          _helpData = jsonDecode(res.body);
          _loading = false;
        });
      } else {
        setState(() {
          _error = true;
          _loading = false;
        });
      }
    } catch (_) {
      setState(() {
        _error = true;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final title = _helpData?['titulo'] ?? (widget.slug == 'realizar-intercambio' ? '¿Cómo realizar un intercambio?' : (widget.slug == 'como-vender' ? '¿Cómo vender?' : '¿Cómo comprar?'));
    final description = _helpData?['descripcion'] ?? '';
    final List pasos = _helpData?['pasos'] ?? [];

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.bold, color: kPrimary),
      ),
      content: SizedBox(
        width: double.maxFinite,
        child: _loading
            ? const SizedBox(
                height: 100,
                child: Center(child: CircularProgressIndicator(color: kPrimary)),
              )
            : _error
                ? const SizedBox(
                    height: 100,
                    child: Center(child: Text('Error al cargar la información de ayuda', style: TextStyle(color: Colors.red))),
                  )
                : SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          description,
                          style: const TextStyle(fontSize: 13, height: 1.4),
                        ),
                        const SizedBox(height: 16),
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: pasos.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 14),
                          itemBuilder: (context, index) {
                            final paso = pasos[index];
                            final pasoImg = paso['imagen']?.toString();
                            final hasImage = pasoImg != null && pasoImg.isNotEmpty && !pasoImg.contains('no-product');
                            
                            return Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: kBgLight,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    paso['titulo'] ?? '',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kTextDark),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    paso['descripcion'] ?? '',
                                    style: const TextStyle(fontSize: 12, color: kTextGray, height: 1.3),
                                  ),
                                  if (hasImage) ...[
                                    const SizedBox(height: 8),
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(6),
                                      child: Image.network(
                                        ApiClient.fixImageUrl(pasoImg),
                                        width: double.infinity,
                                        height: 120,
                                        fit: BoxFit.cover,
                                        errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cerrar', style: TextStyle(color: kPrimary)),
        )
      ],
    );
  }
}
