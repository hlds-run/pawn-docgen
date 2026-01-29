export class FontLoaderService {
  private fontCache: Map<string, ArrayBuffer> = new Map();

  async getFontData(fileName: string): Promise<ArrayBuffer> {
    const cached = this.fontCache.get(fileName);
    if (cached) {
      return cached;
    }

    const path = `./${fileName}`;
    const file = Bun.file(path);

    if (!(await file.exists())) {
      throw new Error(
        `Font file not found: ${path}. Make sure it is in the root directory.`,
      );
    }

    const data = await file.arrayBuffer();
    this.fontCache.set(fileName, data);

    console.log(`[FontLoader] Loaded and cached: ${fileName}`);
    return data;
  }
}
