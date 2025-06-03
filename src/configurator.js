import * as fabric from 'fabric';

// TEMPORARY sanity check
console.log('REST root:', PFS_Settings.rest_url);

document.addEventListener('DOMContentLoaded', () => {
  // ------------------------------------------------------------------
  // 1 – Fabric canvas
  // ------------------------------------------------------------------
  const canvas = new fabric.Canvas('pfs-canvas', { backgroundColor: '#eee' });

  // placeholder 5×7 rectangle
  const frame = new fabric.Rect({
    width: 5 * 90,
    height: 7 * 90,
    fill: '#fff',
    stroke: '#999',
  });
  canvas.add(frame);
  canvas.centerObject(frame);
  canvas.requestRenderAll();

  // ------------------------------------------------------------------
  // 2 – File-picker listener
  // ------------------------------------------------------------------
  const picker = document.getElementById('pfs-upload');
  picker.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    console.log('Chosen file →', file.name);

    /* send to /pfs/v1/upload */
    const form = new FormData();
    form.append('file', file);

	const res = await fetch(`${PFS_Settings.rest_url}upload`, {
	  method: 'POST',
	  headers: { 'X-WP-Nonce': PFS_Settings.nonce },
	  body: form,
	});


    if (!res.ok) {
      alert('Upload failed');
      return;
    }

    const { url } = await res.json();

    /* load returned URL into Fabric */
    let img = await fabric.Image.fromURL(url, { crossOrigin: 'anonymous' });
    if (!(img instanceof fabric.Image)) {
      img = new fabric.Image(img);
    }

    const scale = Math.min(
      canvas.getWidth()  / img.width,
      canvas.getHeight() / img.height
    );
    img.scale(scale);
    img.set({ selectable: false });

    canvas.clear();
    canvas.add(img);
    canvas.centerObject(img);
    canvas.requestRenderAll();
  });
});
