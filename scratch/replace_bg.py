import sys
from PIL import Image, ImageDraw
import math

def process_logo(input_path, output_path, bg_color, tolerance):
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()
    
    new_data = []
    # Replace blue background with white
    for item in data:
        r, g, b, a = item
        # Calculate distance to background color
        dist = math.sqrt((r - bg_color[0])**2 + (g - bg_color[1])**2 + (b - bg_color[2])**2)
        if dist < tolerance:
            # It's background, make it white
            new_data.append((255, 255, 255, 255))
        else:
            # It's part of the logo.
            # To handle anti-aliasing slightly better, we can blend, but for now just keep it
            new_data.append(item)
            
    img.putdata(new_data)
    
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
    # Since the resized logo has a white background, it will just blend with the white circle
    canvas.paste(resized, (offset, offset))
    
    # Apply a circular mask to the FINAL canvas to ensure the edges are transparent
    # (Just in case the pasted square went outside the circle, though at 80% it shouldn't)
    mask = Image.new("L", (img.width, img.height), 0)
    draw_mask = ImageDraw.Draw(mask)
    draw_mask.ellipse((0, 0, img.width, img.height), fill=255)
    
    # Apply mask
    canvas.putalpha(mask)
    
    canvas.save(output_path)

if __name__ == "__main__":
    bg = (78, 161, 213)
    process_logo("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/Subir.APP/icono_512x512.png",
                 "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon_final.png", bg, 50)
    print("Done")
