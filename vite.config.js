import { defineConfig } from 'vite';
export default defineConfig({
  build: {
    outDir: 'build',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        configurator: 'src/configurator.js',
      },
      output: {
        entryFileNames: '[name].js'
      }
    }
  }
});
