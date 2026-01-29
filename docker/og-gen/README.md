<center>
    <img width="1200" height="630" alt="image" src="https://github.com/user-attachments/assets/7d910009-a190-4449-a87d-9c21f5591aeb" />
</center>

# OG-Gen Service

A high-performance Open Graph image generation service for the pawn-docgen project.

## Overview

The `og-gen` service is a TypeScript application that generates dynamic [Open Graph (OG)](https://ogp.me/) images for social media sharing. It renders beautiful, customizable images for documentation pages using React components and the [Takumi](https://takumi.kane.tw) image rendering library.

**Features:**

- 🎨 Dynamic OG image generation from query parameters
- 🔐 HMAC-based security verification
- 🌓 Light and dark theme support
- ⚡ Blazing fast with Bun runtime
- 🎭 Preview mode for development
- 💾 Built-in image caching
- 🎯 1200x630px optimized images

## Architecture

### Clean Architecture Pattern

The `og-gen` service follows **[Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)** principles with strict layer separation:

```
src/
├── domain/              # ← Business logic (no external dependencies)
│   ├── entities/        # OgImage, Theme business objects
│   └── interfaces/      # ImageRenderer, SecurityProvider abstractions
│
├── application/         # ← Use case handlers (CQS Query layer)
│   └── queries/         # GetOgImageQuery, GetOgPreviewQuery (read operations)
│       ├── get-og-image/
│       │   ├── get-og-image.query.ts       # Query object (input)
│       │   └── get-og-image.handler.ts     # Query handler (business logic)
│       └── get-og-preview/
│           ├── get-og-preview.query.ts
│           └── get-og-preview.handler.ts
│
├── infrastructure/      # ← Technical implementations (external tools)
│   ├── crypto/          # HMAC security provider
│   │   └── hmac-security.provider.ts
│   ├── fonts/           # Font loading service
│   │   └── font-loader.service.ts
│   └── renderer/        # Pluggable renderers
│       ├── takumi.renderer.ts      # Rust-based PNG rendering
│       └── html.renderer.ts        # HTML preview rendering
│
├── presentation/        # ← HTTP layer (controllers & components)
│   ├── controllers/     # HTTP request handlers
│   │   ├── og.controller.ts        # /og and /og/preview routes
│   │   └── health.controller.ts    # /health endpoint
│   └── components/      # React components
│       ├── og-layout.component.tsx      # HTML structure
│       └── og-template.component.tsx    # Image content template
│
└── main.ts             # ← Application entry point
```

### Dependency Flow (Inward ↓)

```
Presentation (HTTP) ↓
    ↓
Application (CQS Handlers) ↓
    ↓
Domain (Business Logic) ↓
    ↓
Infrastructure (Implementations)
```

**Key Principle:** Domain layer knows nothing about HTTP, databases, or frameworks.

### CQS (Command Query Separation)

The application layer uses **[CQS](https://en.wikipedia.org/wiki/Command–query_separation)** pattern:

- **Queries** = read operations that return data without side effects
- **Commands** = write operations (not used in this service, but pattern is extensible)

**Example - Query Flow:**

```typescript
// 1. Query object (contains input data)
export class GetOgImageQuery {
  constructor(
    public readonly image: OgImage,
    public readonly signature: string,
  ) {}
}

// 2. Query handler (implements use case logic)
export class GetOgImageHandler {
  constructor(
    private readonly renderer: ImageRenderer<Uint8Array>,
    private readonly security: SecurityProvider,
  ) {}

  async execute(query: GetOgImageQuery): Promise<Uint8Array> {
    // Domain logic here
    const { image, signature } = query;
    const isValid = this.security.verify(image.title, signature);
    if (!isValid) throw new Error("Invalid signature");
    return await this.renderer.render(image);
  }
}

// 3. Controller (HTTP layer calls handler)
async render(req: Request): Promise<Response> {
  const query = new GetOgImageQuery(ogImage, signature);
  const imageBuffer = await this.getOgImageHandler.execute(query);
  return new Response(Buffer.from(imageBuffer), {
    headers: { "Content-Type": "image/png" }
  });
}
```

**Benefits:**

- Testable: Mock query and handler independently
- Reusable: Handlers work with any HTTP framework
- Clear intent: Query names describe operations
- Extensible: Easy to add Commands for write operations

## Configuration

### File Naming Conventions (NestJS-inspired)

The project follows NestJS file naming patterns for clarity and consistency:

| Type                        | Pattern                | Example                                  |
| --------------------------- | ---------------------- | ---------------------------------------- |
| **Domain Entity**           | `{name}.entity.ts`     | `og-image.entity.ts`                     |
| **Interface/Contract**      | `{name}.interface.ts`  | `image-renderer.interface.ts`            |
| **Implementation Provider** | `{name}.provider.ts`   | `hmac-security.provider.ts`              |
| **Service**                 | `{name}.service.ts`    | `font-loader.service.ts`                 |
| **Controller**              | `{name}.controller.ts` | `og.controller.ts`                       |
| **Query Object**            | `{name}.query.ts`      | `get-og-image.query.ts`                  |
| **Query Handler**           | `{name}.handler.ts`    | `get-og-image.handler.ts`                |
| **React Component**         | `{name}.component.tsx` | `og-template.component.tsx`              |
| **Renderer**                | `{name}.renderer.ts`   | `takumi.renderer.ts`, `html.renderer.ts` |

**Directory Structure Rules:**

- Create subdirectories for feature grouping: `queries/get-og-image/`
- Group related files together (query + handler in same folder)
- Use kebab-case for filenames, PascalCase for exported class names

### Environment Variables

```bash
# Server port (default: 3000)
PORT=3000

# HMAC secret for signature verification
OG_HMAC_SECRET=your-secret-key

# Enable HMAC verification (default: false)
CHECK_HMAC=true

# HMAC signature length (default: 8)
CHECK_HMAC_SYMBOLS=8

# Node environment
NODE_ENV=production  # or 'development'
```

See `.env` file for development defaults.

## API Endpoints

### Generate OG Image

**GET** `/og`

Generates a PNG image for the specified parameters.

**Query Parameters:**

- `title` (required) - Image title (max 86 chars, auto-truncated)
- `subtitle` - Image subtitle (max 450 chars, auto-truncated)
- `tag` - Tag/category badge (default: "Pawn")
- `theme` - Theme color ("dark" or "light", default: "dark")
- `s` - HMAC signature (required if CHECK_HMAC=true)

**Response:**

- Content-Type: `image/png`
- Cache-Control: `public, max-age=31536000, immutable` (1 year)

**Example:**

```
GET /og?title=MyFunction&subtitle=Documentation&tag=API&theme=dark&s=abc123def456
```

### Preview HTML

**GET** `/og/preview`

Renders HTML preview of the OG image for development and testing.

**Query Parameters:** Same as `/og` (signature optional in preview mode)

**Response:**

- Content-Type: `text/html; charset=utf-8`

**Example:**

```
GET /og/preview?title=MyFunction&subtitle=Documentation
```

### Health Check

**GET** `/health`

Service health endpoint.

**Response:**

```json
{
  "status": "ok",
  "hmac_enabled": true
}
```

## Security

### HMAC Signature

When `CHECK_HMAC=true`, all `/og` requests require a valid HMAC signature.

**Generation (PHP example):**

```php
$secret = getenv('OG_HMAC_SECRET');
$title = 'MyFunction';
$fullHash = hash_hmac('sha256', $title, $secret);
$signature = substr($fullHash, 0, 8); // CHECK_HMAC_SYMBOLS

$url = "/og?title=$title&s=$signature";
```

**Verification (TypeScript):**

- Signature is validated against the `title` parameter
- Timing-safe comparison prevents timing attacks
- Invalid signatures return 403 Forbidden

## Image Customization

### Theme Colors

**Dark Theme:**

- Background: #141020
- Card: #1e1830
- Text: #e6e6f0
- Accent: #588cff

**Light Theme:**

- Background: #f5f6fa
- Card: white
- Text: #2c1e47
- Accent: #0b5ed7

### Typography

- Font: IBM Plex Sans (Regular 400, SemiBold 600)
- Title: 48px SemiBold
- Subtitle: 24px Regular
- Tag: 14px Bold (uppercase)

## Fonts

Two font files are included in the root directory:

- `IBMPlexSans-Regular.ttf` — Regular weight (400)
- `IBMPlexSans-SemiBold.ttf` — SemiBold weight (600)

These are loaded at runtime and cached for performance.

## Pluggable Renderers

One of the key benefits of Clean Architecture is the ability to **swap implementations easily**. The `og-gen` service demonstrates this with multiple renderer implementations:

### ImageRenderer Interface

```typescript
// domain/interfaces/image-renderer.interface.ts
export interface ImageRenderer<T> {
  render(image: OgImage): Promise<T>;
}
```

All renderers implement this single contract.

### Available Renderers

#### 1. Takumi Renderer (PNG Output)

**File:** `infrastructure/renderer/takumi.renderer.ts`

```typescript
export class TakumiRenderer implements ImageRenderer<Uint8Array> {
  async render(image: OgImage): Promise<Uint8Array> {
    // Uses Rust-compiled Takumi library for fast image rendering
    // Returns PNG binary data
  }
}
```

- ✅ Fast (~50ms per image)
- ✅ Compiled Rust backend
- ✅ Production-ready
- ✅ Supports complex layouts (with [Tailwind CSS](https://tailwindcss.com) out of the box)

#### 2. HTML Renderer (Preview)

**File:** `infrastructure/renderer/html.renderer.ts`

```typescript
export class HtmlRenderer implements ImageRenderer<string> {
  async render(image: OgImage): Promise<string> {
    // Renders React component to HTML string
    // Returns HTML markup for browser preview
  }
}
```

- ✅ Instant rendering
- ✅ No image generation overhead
- ✅ Great for development
- ✅ Uses React SSR ([react-dom/server](https://react.dev/reference/react-dom/server))

### Adding a New Renderer

To add a custom renderer (e.g., Sharp, Puppeteer, Skia):

**Step 1:** Create implementation file

```typescript
// infrastructure/renderer/sharp.renderer.ts
import sharp from "sharp";
import { ImageRenderer } from "../../domain/interfaces/image-renderer.interface";
import { OgImage } from "../../domain/entities/og-image.entity";

export class SharpRenderer implements ImageRenderer<Uint8Array> {
  async render(image: OgImage): Promise<Uint8Array> {
    const svg = await this.generateSvg(image);
    return await sharp(Buffer.from(svg)).png().toBuffer();
  }

  private async generateSvg(image: OgImage): Promise<string> {
    // SVG generation logic
  }
}
```

**Step 2:** Update dependency injection in `main.ts`

```typescript
// main.ts
import { SharpRenderer } from "./infrastructure/renderer/sharp.renderer";

const imageRenderer = new SharpRenderer(); // Swap implementation!
const getOgImageHandler = new GetOgImageHandler(
  imageRenderer,
  securityProvider,
);
```

**That's it!** The handler and controller work without changes.

### Why This Matters

✅ **Testable:** Mock any renderer for unit tests  
✅ **Swappable:** Change rendering library without touching business logic  
✅ **Extensible:** Add new renderers without breaking existing code  
✅ **Future-proof:** Easy to switch to better libraries as they emerge

### Minimal Dependencies Strategy

The project uses **only 3 dependencies** (plus TypeScript types):

```json
{
  "dependencies": {
    "@takumi-rs/image-response": "^0.66.0", // Image rendering
    "react": "^19.2.3", // Component framework
    "react-dom": "^19.2.3" // SSR support
  },
  "devDependencies": {
    "@types/bun": "^1.3.6", // Bun types
    "@types/react": "^19.2.3", // React types
    "@types/react-dom": "^19.2.3", // React-DOM types
    "tailwindcss": "^4.1.18" // CSS utility (optional)
  }
}
```

**No dependencies for:**

- HTTP server (built into Bun)
- HMAC crypto (Bun crypto module)
- Font loading (Bun file API)
- Dependency injection (simple constructor injection)
- React rendering (built-in with react-dom/server)

**Benefits:**

- ⚡ Extremely fast startup (~100ms)
- 📦 Tiny container size (~50MB)
- 🔒 Minimal security surface
- 🚀 No npm package bloat
- 🎯 Clear dependency graph

### Why Bun?

- **All-in-one:** Built-in TypeScript, HTTP server, package manager
- **Fast:** 10x faster than Node.js for startup
- **Minimal:** No external dependencies for basic features
- **Lightweight:** Perfect for single-purpose services
- **Modern:** Native ES modules, top-level await, JSX support

## Development

### Local Setup

```bash
cd docker/og-gen

# Install dependencies
bun install

# Development mode (watch mode)
bun run dev

# Production mode
bun run start
```

Server runs on `http://localhost:3000`

### Testing Architecture in Action

The Clean Architecture makes testing straightforward:

#### Testing a Query Handler

```typescript
// Example: Test GetOgImageHandler without HTTP/Bun

import { GetOgImageHandler } from "../src/application/queries/get-og-image/get-og-image.handler";
import { OgImage } from "../src/domain/entities/og-image.entity";
import { Theme } from "../src/domain/entities/theme.entity";

// Mock implementations for testing
class MockRenderer implements ImageRenderer<Uint8Array> {
  async render(image: OgImage): Promise<Uint8Array> {
    return new Uint8Array([1, 2, 3]); // Fake PNG data
  }
}

class MockSecurityProvider implements SecurityProvider {
  verify(data: string, hash: string): boolean {
    return hash === "valid-hash";
  }
}

// Test the handler
const renderer = new MockRenderer();
const security = new MockSecurityProvider();
const handler = new GetOgImageHandler(renderer, security);

const image = new OgImage({
  title: "Test Function",
  subtitle: "Test Description",
  tag: "Pawn API",
  theme: Theme.fromString("dark"),
});

const query = new GetOgImageQuery(image, "valid-hash");
const result = await handler.execute(query);

console.assert(result.length > 0, "Should return image data");
```

**Why this is great:**

- ✅ No HTTP framework needed
- ✅ No async test runners required
- ✅ Easy to mock dependencies
- ✅ Fast execution
- ✅ Clear what's being tested

#### Swapping Renderers for Testing

```typescript
// Use HTML renderer in tests for instant feedback
const testRenderer = new HtmlRenderer();
const handler = new GetOgImageHandler(testRenderer, securityProvider);

// Or mock renderer for snapshot testing
class SnapshotRenderer implements ImageRenderer<Uint8Array> {
  async render(image: OgImage): Promise<Uint8Array> {
    // Return consistent data for snapshot comparison
    return Buffer.from(
      JSON.stringify({
        title: image.title,
        theme: image.theme.isDark() ? "dark" : "light",
      }),
    );
  }
}
```

### Testing Preview Mode

```bash
# Without HMAC verification (development)
curl "http://localhost:3000/og/preview?title=TestFunction&subtitle=Test%20subtitle"

# Open in browser to see rendered preview
```

### API Testing Examples

```bash
# Health check
curl http://localhost:3000/health | jq

# Generate PNG with signature (if CHECK_HMAC=true)
curl "http://localhost:3000/og?title=MyFunc&theme=dark&s=abc123" \
  -H "Accept: image/png" \
  -o og-image.png

# Generate with preview (no signature needed)
curl "http://localhost:3000/og/preview?title=MyFunc&subtitle=Docs" > preview.html
open preview.html
```

## Performance

- **Rendering:** ~50ms per image (Takumi compiled library)
- **Caching:** Font files cached in memory after first load
- **Cache-Control:** Images cached for 1 year (immutable)
- **NGINX:** 30-day reverse proxy cache with stale-while-revalidate

## Docker

### Build

```bash
docker build -t og-gen:latest .
```

### Run

```bash
docker run -p 3000:3000 \
  -e PORT=3000 \
  -e OG_HMAC_SECRET=secret \
  -e CHECK_HMAC=true \
  -e NODE_ENV=production \
  og-gen:latest
```

### Environment (docker-compose.yml)

```yaml
og-gen:
  build: ./docker/og-gen
  restart: always
  env_file: .env
  ports:
    - "3000:3000"
```

## Integration with PHP

The main `www/template/header.php` generates OG image URLs:

```php
$OG_Params = [
    'title' => $PageFunction['Function'],
    'subtitle' => $PageFunction['Comment'],
    'tag' => 'Pawn API',
    'theme' => 'dark'
];

// Generate HMAC signature
$fullSignature = hash_hmac('sha256', $OG_Params['title'], getenv('OG_HMAC_SECRET'));
$signature = substr($fullSignature, 0, getenv('CHECK_HMAC_SYMBOLS'));

// Construct URL
$ogImageUrl = "/og?" . http_build_query($OG_Params) . "&s=" . $signature;
```

## Troubleshooting

### Image rendering fails

- Ensure font files exist in the container root
- Verify Takumi library version compatibility

### HMAC signature invalid

- Check secret matches in PHP and og-gen config
- Verify signature is calculated on `title` parameter only
- Ensure signature length matches `CHECK_HMAC_SYMBOLS`

### Fonts not loading

```
Error: Font file not found: ./IBMPlexSans-Regular.ttf
```

Solution: Ensure TTF files are in `docker/og-gen/` directory before building.
