import sys
from PIL import Image

def process_logo(input_path, output_path):
    img = Image.open(input_path).convert("RGBA")
    
    # Crop the right square
    size = min(img.width, img.height)
    left = img.width - size
    cropped = img.crop((left, 0, img.width, size))
    
    # Resize to 512x512 to save space and time
    resized = cropped.resize((512, 512), Image.LANCZOS)
    
    resized.save(output_path)

if __name__ == "__main__":
    process_logo("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/public/imgs/logoTypes/header-logo.png",
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/scratch/logo_right.png")
    print("Done")
