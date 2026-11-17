import { expect, test } from "bun:test";
import { ImageRendererBusyError } from "../../application/errors/image-renderer-busy.error";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";
import { LimitedImageRenderer } from "./limited-image.renderer";

test("the renderer decorator limits concurrent work and forwards results", async () => {
  let release!: () => void;
  let calls = 0;
  const delegate = {
    render: async () => {
      calls++;
      await new Promise<void>((resolve) => { release = resolve; });
      return new Uint8Array([42]);
    },
  };
  const renderer = new LimitedImageRenderer(delegate, 1, 0);
  const image = new OgImage({ title: "Test", theme: Theme.fromString("dark") });
  const first = renderer.render(image);
  await expect(renderer.render(image)).rejects.toBeInstanceOf(ImageRendererBusyError);
  expect(calls).toBe(1);
  release();
  expect([...await first]).toEqual([42]);
});
