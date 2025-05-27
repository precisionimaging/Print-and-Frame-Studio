import * as fabric from 'fabric';

document.addEventListener('DOMContentLoaded', () => {
  /* canvas */
  const canvas = new fabric.Canvas('pfs-canvas', { backgroundColor: '#eee' });

  /* placeholder 5×7 rectangle */
  const frame = new fabric.Rect({
    width : 5 * 90,
    height: 7 * 90,
    fill  : '#fff',
    stroke: '#999',
  });
  canvas.add(frame);
  canvas.centerObject(frame);
  canvas.requestRenderAll();

  /* upload listener – THIS is the part we changed */
  const picker = document.getElementById('pfs-upload');

	picker.addEventListener('change', async (e) => {
	  const file = e.target.files[0];
	  if (!file) return;

	  console.log('Chosen file →', file.name);
	  const url = URL.createObjectURL(file);

	  try {
		let img = await fabric.Image.fromURL(url, { crossOrigin: 'anonymous' });

		if (!(img instanceof fabric.Image)) {
		  img = new fabric.Image(img);
		}

		const scale = Math.min(
		  canvas.getWidth()  / img.width,
		  canvas.getHeight() / img.height
		);
		img.scale(scale);                    // ← separate call
		img.set({ selectable: false });      // ← now safe

		canvas.clear();
		canvas.add(img);
		canvas.centerObject(img);
		canvas.requestRenderAll();
	  } finally {
		URL.revokeObjectURL(url);
	  }
	});


});
