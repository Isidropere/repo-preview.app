import sys
from PIL import Image, ImageDraw

def process_logo(input_path, output_path):
    img = Image.open(input_path).convert("RGBA")
    
    # Scale down to 55% to fit perfectly within Android's Adaptive Icon safe zone (which is ~66%)
    target_size = 512 * 0.55
    scale = target_size / max(img.width, img.height)
    new_w = int(img.width * scale)
    new_h = int(img.height * scale)
    
    resized = img.resize((new_w, new_h), Image.LANCZOS)
    
    # Create a 512x512 transparent canvas
    canvas = Image.new("RGBA", (512, 512), (0, 0, 0, 0))
    
    # Draw a white circle
    draw = ImageDraw.Draw(canvas)
    draw.ellipse((0, 0, 512, 512), fill=(255, 255, 255, 255))
    
    # Calculate offset to center the logo
    offset_x = (512 - new_w) // 2
    offset_y = (512 - new_h) // 2
    
    # Paste the transparent logo on top of the white circle (using the logo as a mask)
    canvas.paste(resized, (offset_x, offset_y), mask=resized)
    
    # Apply a circular mask to the FINAL canvas to ensure the edges are perfectly smooth and transparent outside
    mask = Image.new("L", (512, 512), 0)
    draw_mask = ImageDraw.Draw(mask)
    draw_mask.ellipse((0, 0, 512, 512), fill=255)
    canvas.putalpha(mask)
    
    # Save the final round icon
    canvas.save(output_path)
    
if __name__ == "__main__":
    process_logo("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/public/imgs/logoTypes/logoFooter.png", 
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon_final.png")
    print("Done")
