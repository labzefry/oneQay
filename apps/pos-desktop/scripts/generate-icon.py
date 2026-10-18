# Original code-native oneQay mark. Standard library only; deterministic Windows ICO.
from pathlib import Path
import struct
size=64
pixels=bytearray()
for y in range(size-1,-1,-1):
 for x in range(size):
  circle=(x-31.5)**2+(y-31.5)**2
  ring=13**2<circle<22**2
  tail=39<=x<=49 and 39<=y<=49 and abs(x-y)<=3
  rounded=(x>=8 and x<56)or(y>=8 and y<56)or(min((x-8)**2,(x-55)**2)+min((y-8)**2,(y-55)**2)<=64)
  rgba=(255,255,255,255)if ring or tail else(102,120,229,255 if rounded else 0)
  pixels.extend((rgba[2],rgba[1],rgba[0],rgba[3]))
mask=bytes(((size+31)//32)*4*size)
bmp=struct.pack('<IiiHHIIiiII',40,size,size*2,1,32,0,len(pixels)+len(mask),0,0,0,0)+pixels+mask
ico=struct.pack('<HHH',0,1,1)+struct.pack('<BBBBHHII',size,size,0,0,1,32,len(bmp),22)+bmp
path=Path(__file__).resolve().parents[1]/'src-tauri/icons/icon.ico'
path.parent.mkdir(parents=True,exist_ok=True)
path.write_bytes(ico)
