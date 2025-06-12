import * as fabric from 'fabric';

document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------
   * 0 –  Canvas + placeholder
   * ---------------------------------------------------------------- */
  const canvas = new fabric.Canvas('pfs-canvas', { backgroundColor: '#eee' });

  const placeholder = new fabric.Rect({
    width : 5 * 90,
    height: 7 * 90,
    fill  : '#fff',
    stroke: '#999',
  });
  canvas.add(placeholder);
  canvas.centerObject(placeholder);
  canvas.requestRenderAll();

	/* ------------------------------------------------------------------
	 * MAT LAYER  – create lazily, always lives at index 0
	 * ---------------------------------------------------------------- */
	let matRect = null;

	function updateMat(hex, thicknessPx) {
	  if (!matRect) {
		// first call: build the rectangle and slot it at layer 0
		matRect = new fabric.Rect({ selectable: false, evented: false });
		canvas.insertAt(matRect, 0, false);   // index 0 (behind everything)
	  }

	  matRect.set({
		left   : thicknessPx,
		top    : thicknessPx,
		width  : canvas.getWidth()  - thicknessPx * 2,
		height : canvas.getHeight() - thicknessPx * 2,
		fill   : hex,
	  });

	  canvas.requestRenderAll();
	}


  /* ------------------------------------------------------------------
   * 2 –  Frame helper
   * ---------------------------------------------------------------- */
	/**
	 * Draws the selected moulding around the current canvas.
	 * @param {Object} frame   – catalogue item (contains .png and .rate_ui)
	 *        frame.png keys : t, r, b, l, tl, tr, bl, br  (top / right / …)
	 */
	function applyFrame(frame) {

	  /* 0 ─────── clear previous edges */
	  canvas.getObjects().forEach(o => {
		if (o.frameEdge) canvas.remove(o);
	  });

	  /* 1 ─────── helpers & geometry */
	  const { png } = frame;
	  const pw   = canvas.getWidth();
	  const ph   = canvas.getHeight();
	  const edge = 90;                       // 1 in @ 90 ppi

	  const load = url =>
		new Promise(resolve =>
		  fabric.Image.fromURL(
			url,
			img => resolve(img),
			{ crossOrigin: 'anonymous' }
		  )
		);

	  /* 2 ─────── load & place the four long edges */
	  Promise.all([
		// top
		load(png.t).then(img => {
		  img.scaleToHeight(edge);
		  img.scaleToWidth(pw);
		  img.set({ left: 0, top: 0, frameEdge: true, selectable: false });
		  canvas.add(img);
		}),
		// right
		load(png.r).then(img => {
		  img.scaleToWidth(edge);
		  img.scaleToHeight(ph);
		  img.set({ left: pw - edge, top: 0, frameEdge: true, selectable: false });
		  canvas.add(img);
		}),
		// bottom
		load(png.b).then(img => {
		  img.scaleToHeight(edge);
		  img.scaleToWidth(pw);
		  img.set({ left: 0, top: ph - edge, frameEdge: true, selectable: false });
		  canvas.add(img);
		}),
		// left
		load(png.l).then(img => {
		  img.scaleToWidth(edge);
		  img.scaleToHeight(ph);
		  img.set({ left: 0, top: 0, frameEdge: true, selectable: false });
		  canvas.add(img);
		})
	  ]).then(() => {

		/* 3 ─────── add corners on TOP of the edges */
		const addCorner = (url, left, top) =>
		  fabric.Image.fromURL(
			url,
			img => {
			  img.set({ left, top, frameEdge: true, selectable: false });
			  canvas.add(img);
			},
			{ crossOrigin: 'anonymous' }
		  );

		addCorner(png.tl, 0,          0);
		addCorner(png.tr, pw - edge,  0);
		addCorner(png.bl, 0,         ph - edge);
		addCorner(png.br, pw - edge, ph - edge);

		/* 4 ─────── make sure the mat layer is refreshed */
		const matHex = document.getElementById('pfs-mat-select').value;
		updateMat(matHex, 60);                  // 60 px = ⅔ in at 90 ppi

		canvas.requestRenderAll();
	  });
	}

  /* ------------------------------------------------------------------
   * 3 –  File-upload handler (unchanged)
   * ---------------------------------------------------------------- */
  document.getElementById('pfs-upload').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;
    console.log('Chosen file →', file.name);

    const form = new FormData();
    form.append('file', file);

    const res = await fetch(`${PFS_Settings.rest_url}upload`, {
      method : 'POST',
      headers: { 'X-WP-Nonce': PFS_Settings.nonce },
      body   : form,
    });
    if (!res.ok) { alert('Upload failed'); return; }

    const { url } = await res.json();
    let img = await fabric.Image.fromURL(url, { crossOrigin:'anonymous' });
    if (!(img instanceof fabric.Image)) img = new fabric.Image(img);

    const scale = Math.min(
      canvas.getWidth()  / img.width,
      canvas.getHeight() / img.height
    );
    img.scale(scale); img.set({ selectable:false });

    canvas.clear();
    canvas.add(matRect);            // keep mat layer
    canvas.add(img);
    canvas.centerObject(img);
    canvas.requestRenderAll();
  });

  /* ------------------------------------------------------------------
   * 4 –  Load catalogue & populate dropdowns
   * ---------------------------------------------------------------- */
  fetch(`${PFS_Settings.rest_url}assets`)
    .then(r => r.json())
    .then(({ frames, mats }) => {
      const frameSel = document.getElementById('pfs-frame-select');
      const matSel   = document.getElementById('pfs-mat-select');

      frames.forEach(f => {
        const o=document.createElement('option');
        o.value=f.slug; o.textContent=f.name; frameSel.appendChild(o);
      });
      mats.forEach(m => {
        const o=document.createElement('option');
        o.value=m.hex; o.textContent=m.name; matSel.appendChild(o);
      });

      if (frames.length) applyFrame(frames[0]);
      if (mats.length)   updateMat(mats[0].hex, 60);

      frameSel.addEventListener('change', () =>
        applyFrame(frames.find(f => f.slug===frameSel.value)));
      matSel.addEventListener('change', () =>
        updateMat(matSel.value,60));
    });
});
