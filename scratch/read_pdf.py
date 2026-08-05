import os
import sys

pdf_path = r"c:\Users\iperez\Desktop\datos\repos\copi\CB.app\imgAnalizar\Documento técnico - Página de Pago AZUL.PDF"
txt_output_path = r"c:\Users\iperez\Desktop\datos\repos\copi\CB.app\scratch\azul_pdf_text.txt"

def try_extract():
    # Try importing pypdf
    try:
        import pypdf
        print("Using pypdf")
        reader = pypdf.PdfReader(pdf_path)
        text = ""
        for page in reader.pages:
            text += page.extract_text() + "\n"
        return text
    except ImportError:
        pass

    # Try importing pdfplumber
    try:
        import pdfplumber
        print("Using pdfplumber")
        text = ""
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                text += page.extract_text() + "\n"
        return text
    except ImportError:
        pass

    # Try importing fitz (PyMuPDF)
    try:
        import fitz
        print("Using PyMuPDF")
        doc = fitz.open(pdf_path)
        text = ""
        for page in doc:
            text += page.get_text() + "\n"
        return text
    except ImportError:
        pass

    return None

def main():
    if not os.path.exists(pdf_path):
        print(f"Error: PDF file not found at {pdf_path}")
        return

    text = try_extract()
    if text is None:
        print("No PDF library found. Installing pypdf...")
        import subprocess
        subprocess.check_call([sys.executable, "-m", "pip", "install", "pypdf"])
        text = try_extract()

    if text:
        with open(txt_output_path, "w", encoding="utf-8") as f:
            f.write(text)
        print(f"Successfully extracted text to {txt_output_path}")
    else:
        print("Failed to extract text.")

if __name__ == "__main__":
    main()
