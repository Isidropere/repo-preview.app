import sys
from PIL import Image, ImageDraw

def process_icon(input_path, output_path, scale_factor):
    # Open the transparent image
    img = Image.open(input_path).convert("RGBA")
    
    # Calculate new size for the logo
    new_w = int(img.width * scale_factor)
    new_h = int(img.height * scale_factor)
    
    # Resize image
    resized = img.resize((new_w, new_h), Image.LANCZOS)
    
    # Create a new transparent image
    canvas = Image.new("RGBA", (img.width, img.height), (0, 0, 0, 0))
    
    # Draw a white circle
    draw = ImageDraw.Draw(canvas)
    draw.ellipse((0, 0, img.width, img.height), fill=(255, 255, 255, 255))
    
    # Calculate position to paste the resized image in the center
    offset_x = (img.width - new_w) // 2
    offset_y = (img.height - new_h) // 2
    
    # Paste the transparent logo on top of the white circle (using the logo as a mask)
    canvas.paste(resized, (offset_x, offset_y), mask=resized)
    
    # Save the final round icon
    canvas.save(output_path)
    
if __name__ == "__main__":
    process_icon("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/scratch/logo_cropped.png", 
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon_final.png", 0.75)
    print("Done")
