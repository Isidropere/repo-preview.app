import sys
from PIL import Image, ImageDraw

def process_logo(input_path, output_path, bg_color):
    img = Image.open(input_path).convert("RGBA")
    
    # Floodfill the outside background with pure white (or transparency)
    # We will floodfill with magenta first to see what it replaces, 
    # but we can just floodfill with white directly since the circle background is white anyway.
    ImageDraw.floodfill(img, (0, 0), (255, 255, 255, 255), thresh=30)
    
    # Scale it down so it fits inside the circle without touching the edges
    scale = 0.8
    new_size = int(img.width * scale)
    resized = img.resize((new_size, new_size), Image.LANCZOS)
    
    # Create a transparent canvas
    canvas = Image.new("RGBA", (img.width, img.height), (0, 0, 0, 0))
    
    # Draw a white circle
    draw = ImageDraw.Draw(canvas)
    draw.ellipse((0, 0, img.width, img.height), fill=(255, 255, 255, 255))
    
    # Calculate offset
    offset = (img.width - new_size) // 2
    
    # Paste the resized white-background logo onto the canvas
    canvas.paste(resized, (offset, offset))
    
    # Apply a circular mask to the FINAL canvas to ensure the edges are transparent
    mask = Image.new("L", (img.width, img.height), 0)
    draw_mask = ImageDraw.Draw(mask)
    draw_mask.ellipse((0, 0, img.width, img.height), fill=255)
    
    # Apply mask
    canvas.putalpha(mask)
    
    canvas.save(output_path)

if __name__ == "__main__":
    bg = (78, 161, 213)
    process_logo("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/Subir.APP/icono_512x512.png",
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon_final.png", bg)
    print("Done")
