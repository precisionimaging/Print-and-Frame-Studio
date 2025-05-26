import * as fabric from 'fabric';

document.addEventListener('DOMContentLoaded', () => {
  const el     = document.getElementById('pfs-canvas');
  const canvas = new fabric.Canvas(el, { backgroundColor: '#eee' });

  const photo = new fabric.Rect({
    width: 5 * 90,   // 5 in @ 90 ppi
    height: 7 * 90,  // 7 in
    fill: '#fff',
    stroke: '#999'
  });

  canvas.add(photo).centerObject(photo);
});
