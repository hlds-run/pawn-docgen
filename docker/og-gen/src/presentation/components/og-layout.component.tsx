import type { ReactNode } from "react";

interface OgLayoutProps {
  children: ReactNode;
}

export const OgLayout = ({ children }: OgLayoutProps) => {
  return (
    <html lang="ru">
      <head>
        <meta charSet="UTF-8" />
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link
          href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600;700&display=swap"
          rel="stylesheet"
        />
        <style>{`
          body { 
            margin: 0; 
            display: flex;
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            font-family: 'IBM Plex Sans', sans-serif;
          }
          .og-canvas {
            min-width: 1200px;
            width: 1200px;
            height: 630px;
            min-height: 630px;
            overflow: hidden;
            display: flex;
          }
        `}</style>
      </head>
      <body>
        <div className="og-canvas">{children}</div>
      </body>
    </html>
  );
};
