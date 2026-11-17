import { expect, test } from "bun:test";
import { OgImage } from "../../domain/entities/og-image.entity";
import { Theme } from "../../domain/entities/theme.entity";
import { FontLoaderService } from "../fonts/font-loader.service";
import { TakumiRenderer } from "./takumi.renderer";

test("renders PNG data matching the response content type", async () => {
  const renderer = new TakumiRenderer(new FontLoaderService());
  const image = new OgImage({ title: "Test", theme: Theme.fromString("dark") });
  const bytes = await renderer.render(image);
  expect([...bytes.slice(0, 8)]).toEqual([137, 80, 78, 71, 13, 10, 26, 10]);
});
