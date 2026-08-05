import pypdf

pdf_path = r"c:\Users\iperez\Desktop\datos\repos\copi\CB.app\imgAnalizar\Documento técnico - Página de Pago AZUL.PDF"
reader = pypdf.PdfReader(pdf_path)

pages_to_check = [5, 9, 32, 43]

for p_num in pages_to_check:
    print("="*40)
    print(f"PAGE {p_num}")
    print("="*40)
    print(reader.pages[p_num - 1].extract_text())
    print("\n")
