import re

pdf_text_path = r"c:\Users\iperez\Desktop\datos\repos\copi\CB.app\scratch\azul_pdf_text.txt"

with open(pdf_text_path, "r", encoding="utf-8") as f:
    text = f.read()

# Search for any sequence of digits (between 12 and 19 digits)
digit_sequences = re.findall(r'\b\d{12,19}\b', text)
print("Digit sequences of length 12-19 found:")
for seq in set(digit_sequences):
    print(f"- {seq}")

# Search for sequences of digits with spaces or hyphens
formatted_sequences = re.findall(r'\b(?:\d[ -]*?){12,19}\b', text)
print("\nFormatted digit sequences (with spaces/dashes) found:")
for seq in set(formatted_sequences):
    clean = re.sub(r'[^0-9]', '', seq)
    if 12 <= len(clean) <= 19:
        print(f"- {seq} (Clean: {clean})")

# Let's also print all lines containing "Visa", "Mastercard", "tarjeta", "pruebas", or "test"
print("\nLines of interest:")
lines = text.split('\n')
for i, line in enumerate(lines):
    if any(word in line.lower() for word in ["visa", "mastercard", "prueba", "test", "card", "sgs-"]):
        print(f"L{i+1}: {line.strip()}")
