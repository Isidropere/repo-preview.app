import sys
from PIL import Image

def remove_blue_bg(input_path, output_path):
    img = Image.open(input_path).convert("RGBA")
    data = img.getdata()
    
    new_data = []
    for item in data:
        # The background in the screenshot seems to be a solid light blue.
        # We need to detect it. Let's find the most common color in the corners.
        pass
        
    # Actually, a better way is to flood fill from the corner (0,0) with transparency.
    from PIL import ImageDraw
    ImageDraw.floodfill(img, (0, 0), (255, 255, 255, 0), thresh=20)
    
    img.save(output_path)
    
if __name__ == "__main__":
    remove_blue_bg("C:/Users/iperez/Desktop/datos/repos/copi/CB.app/cambialo_app/icon.png", 
                   "C:/Users/iperez/Desktop/datos/repos/copi/CB.app/scratch/icon_transparent.png")
    print("Done")
