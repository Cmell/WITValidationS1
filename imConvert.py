"""Convert the images to the right sizes.

This converts the size of the images in "OriginalStims," and saves them
elsewhere.
"""

from PIL import Image
import os
import glob


def resizeIt(fl, size):
    """Do resizing with PILLOW.

    Args:
        fl (str): A filename.
        size (2-tuple): The size in pixels.
    """
    im = Image.open(fl)
    im2 = im.resize(size)
    return(im2)


guns = glob.glob('./OriginalStims/GrayGuns/*.png')
noguns = glob.glob('./OriginalStims/GrayNonguns/*.png')
hicon = glob.glob('./OriginalStims/HiCon/*.png')
locon = glob.glob('./OriginalStims/LoCon/*.png')

objSize = (380, 380)
faceH = 380
faceW = int(float(faceH) / 420.0 * 300.0)
faceSize = (faceW, faceH)
print faceSize

for i in guns:
    imToSave = resizeIt(i, objSize)
    imToSave.save(os.path.join('GrayGuns', os.path.basename(i)))
print "finished guns"

for i in noguns:
    imToSave = resizeIt(i, objSize)
    imToSave.save(os.path.join('GrayNonguns', os.path.basename(i)))
print "finished noguns"

for i in hicon:
    imToSave = resizeIt(i, faceSize)
    imToSave.save(os.path.join('HiCon', os.path.basename(i)))
print "finished hicon"

for i in hicon:
    imToSave = resizeIt(i, faceSize)
    imToSave.save(os.path.join('LoCon', os.path.basename(i)))
print "finished locon"

resizeIt('./Mask1.png', (380, 380)).save('MaskReal.png')
#resizeIt('./X.png', (200, 200)).save('XReal.png')
#resizeIt('./Check.png', (200, 200)).save('CheckReal.png')
