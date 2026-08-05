import pypdf

pdf_path = r"c:\Users\iperez\Desktop\datos\repos\copi\CB.app\imgAnalizar\Documento técnico - Página de Pago AZUL.PDF"

reader = pypdf.PdfReader(pdf_path)
print(f"Total pages: {len(reader.pages)}")

for i, page in enumerate(reader.pages):
    text = page.extract_text()
    print(f"Page {i+1}: {len(text)} characters")
    if len(text.strip()) < 100:
        print(f"  Warning: Very little text. Images/scans present: {len(page.images)} images")
