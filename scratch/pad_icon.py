import sys
from PIL import Image

def process_icon(input_path, output_path, scale_factor):
    # Open the image
    img = Image.open(input_path).convert("RGBA")
    
    # Calculate new size
    new_w = int(img.width * scale_factor)
    new_h = int(img.height * scale_factor)
    
    # Resize image
    resized = img.resize((new_w, new_h), Image.LANCZOS)
    
    # Create a new transparent image for the foreground (Android)
    # The canvas needs to be the original size
    canvas = Image.new("RGBA", (img.width, img.height), (255, 255, 255, 0))
    
    # Calculate position to paste the resized image in the center
    offset_x = (img.width - new_w) // 2
    offset_y = (img.height - new_h) // 2
    
    # Paste
    canvas.paste(resized, (offset_x, offset_y), resized)
    
    # Save the padded foreground
    canvas.save(output_path)
    
    # Now create a white background version for iOS
    white_bg = Image.new("RGBA", (img.width, img.height), (255, 255, 255, 255))
    white_bg.paste(resized, (offset_x, offset_y), resized)
    white_bg.save(output_path.replace(".png", "_ios.png"))
    
if __name__ == "__main__":
    process_icon("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon.png", 
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon_padded.png", 0.65)
    print("Done")
