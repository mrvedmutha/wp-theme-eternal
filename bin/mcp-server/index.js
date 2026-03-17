#!/usr/bin/env node

/**
 * WP Rig Documentation MCP Server
 * 
 * This server acts as a bridge between AI agents and the official WP Rig 
 * documentation hosted on wprig.io. It fetches live data via the WordPress 
 * REST API and converts it to Markdown.
 * 
 * @package wprig
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import TurndownService from 'turndown';

const turndownService = new TurndownService();

// Initialize the MCP server
const server = new Server(
  {
    name: "wprig-docs-bridge",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

const API_BASE = "https://wprig.io/wp-json/wp/v2/documentation";

/**
 * Tool definitions
 */
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "search_wprig_docs",
        description: "Search the WP Rig documentation at wprig.io. Use this to find how-to guides, architectural explanations, and best practices.",
        inputSchema: {
          type: "object",
          properties: {
            query: {
              type: "string",
              description: "Search term to find relevant documentation.",
            },
          },
          required: ["query"],
        },
      },
      {
        name: "get_wprig_doc",
        description: "Retrieve the full content of a specific WP Rig documentation page by its slug.",
        inputSchema: {
          type: "object",
          properties: {
            slug: {
              type: "string",
              description: "The URL slug of the documentation post (e.g., 'creating-components').",
            },
          },
          required: ["slug"],
        },
      },
    ],
  };
});

/**
 * Tool execution logic
 */
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    if (name === "search_wprig_docs") {
      const query = args.query;
      const response = await fetch(`${API_BASE}?search=${encodeURIComponent(query)}&_fields=id,title,slug,link,excerpt`);
      
      if (!response.ok) {
        throw new Error(`Failed to fetch from wprig.io: ${response.statusText}`);
      }
      
      const posts = await response.json();
      
      if (!Array.isArray(posts) || posts.length === 0) {
        return {
          content: [{ type: "text", text: `No documentation found for "${query}". Try different keywords.` }],
        };
      }

      const results = posts.map(p => {
        const title = p.title.rendered;
        const slug = p.slug;
        const excerpt = turndownService.turndown(p.excerpt.rendered)
          .replace(/\n/g, ' ')
          .trim()
          .slice(0, 150) + '...';
        return `### ${title}\n- **Slug:** \`${slug}\`\n- **Excerpt:** ${excerpt}\n- **Link:** ${p.link}`;
      }).join('\n\n');

      return {
        content: [{ type: "text", text: `Found ${posts.length} documentation items for "${query}":\n\n${results}` }],
      };
    }

    if (name === "get_wprig_doc") {
      const slug = args.slug;
      const response = await fetch(`${API_BASE}?slug=${encodeURIComponent(slug)}`);
      
      if (!response.ok) {
        throw new Error(`Failed to fetch from wprig.io: ${response.statusText}`);
      }
      
      const posts = await response.json();
      if (!Array.isArray(posts) || posts.length === 0) {
        return {
          content: [{ type: "text", text: `Documentation with slug "${slug}" not found.` }],
          isError: true,
        };
      }

      const post = posts[0];
      const markdown = turndownService.turndown(post.content.rendered);
      
      return {
        content: [
          { 
            type: "text", 
            text: `# ${post.title.rendered}\n\n${markdown}\n\n---\n*Source: ${post.link}*` 
          }
        ],
      };
    }

    throw new Error(`Unknown tool: ${name}`);
  } catch (error) {
    return {
      content: [{ type: "text", text: `Error: ${error.message}` }],
      isError: true,
    };
  }
});

/**
 * Start the server
 */
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  // Note: Standard output is reserved for MCP communication.
  // We use error output for logging.
  console.error("WP Rig Documentation MCP Server started successfully.");
}

main().catch((error) => {
  console.error("Fatal server error:", error);
  process.exit(1);
});
