import { describe, expect, mock, test } from "bun:test";
import { GetOgImageHandler } from "../../application/queries/get-og-image/get-og-image.handler";
import { GetOgPreviewHandler } from "../../application/queries/get-og-preview/get-og-preview.handler";
import { ImageRendererBusyError } from "../../application/errors/image-renderer-busy.error";
import { OgController } from "./og.controller";

describe("OG request handling", () => {
  const imageExecute = mock(async () => new Uint8Array([1, 2, 3]));
  const previewExecute = mock(async () => "<html></html>");
  const controller = new OgController(
    { execute: imageExecute } as unknown as GetOgImageHandler,
    { execute: previewExecute } as unknown as GetOgPreviewHandler,
  );

  for (const path of ["/og", "/og/preview"]) {
    test(`${path} rejects missing or blank title before rendering`, async () => {
      imageExecute.mockClear();
      previewExecute.mockClear();
      for (const query of ["", "?title=", "?title=%20%20"]) {
        const req = new Request(`http://localhost${path}${query}`);
        const response = path === "/og" ? await controller.render(req) : await controller.preview(req);
        expect(response.status).toBe(400);
        expect(await response.text()).toBe("title is required");
      }
      expect(imageExecute).not.toHaveBeenCalled();
      expect(previewExecute).not.toHaveBeenCalled();
    });
  }

  test("valid image returns PNG and cache headers", async () => {
    const response = await controller.render(new Request("http://localhost/og?title=Test"));
    expect(response.status).toBe(200);
    expect(response.headers.get("content-type")).toBe("image/png");
    expect(response.headers.get("cache-control")).toContain("immutable");
    expect([...new Uint8Array(await response.arrayBuffer())]).toEqual([1, 2, 3]);
  });

  test("saturation returns 429 and retry hint", async () => {
    const busy = new OgController(
      { execute: async () => { throw new ImageRendererBusyError(); } } as unknown as GetOgImageHandler,
      { execute: previewExecute } as unknown as GetOgPreviewHandler,
    );
    const response = await busy.render(new Request("http://localhost/og?title=Test"));
    expect(response.status).toBe(429);
    expect(response.headers.get("retry-after")).toBe("5");
  });

  test("invalid signatures still return 403", async () => {
    const denied = new OgController(
      { execute: async () => { throw new Error("Invalid signature"); } } as unknown as GetOgImageHandler,
      { execute: previewExecute } as unknown as GetOgPreviewHandler,
    );
    expect((await denied.render(new Request("http://localhost/og?title=Test"))).status).toBe(403);
  });
});
