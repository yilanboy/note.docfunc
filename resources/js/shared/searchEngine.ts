import FlexSearch from 'flexsearch';

const { Document } = FlexSearch;

export interface NoteSearchItem {
    category: string;
    categoryName: string;
    slug: string;
    title: string;
    content: string;
}

export interface SearchResult {
    category: string;
    categoryName: string;
    slug: string;
    title: string;
    snippet: string;
    highlightedTitle: string;
    highlightedSnippet: string;
    score: number;
}

/**
 * Custom tokenizer supporting both Latin/alphanumeric words and CJK unigram + bigram splitting.
 */
export function tokenizeSearch(str: string): string[] {
    if (!str) return [];

    const normalized = str.normalize('NFKC').toLowerCase();
    const tokens = new Set<string>();

    const words = normalized.split(/[\s,.;:!?()[\]{}<>/\\~@#$%^&*+=|`"'_-]+/);
    for (const word of words) {
        if (!word) continue;
        tokens.add(word);

        const cjkRegex = /[\u4e00-\u9fa5\u3040-\u30ff\uac00-\ud7af]+/g;
        let match: RegExpExecArray | null;
        while ((match = cjkRegex.exec(word)) !== null) {
            const run = match[0];
            const chars = Array.from(run);
            for (let i = 0; i < chars.length; i++) {
                tokens.add(chars[i]);
                if (i + 1 < chars.length) {
                    tokens.add(chars[i] + chars[i + 1]);
                }
            }
        }
    }

    return Array.from(tokens);
}

function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Highlight search terms within text with high-contrast mark elements.
 */
export function highlightMatch(text: string, query: string): string {
    if (!text || !query.trim()) {
        return escapeHtml(text);
    }

    const normalizedQuery = query.normalize('NFKC').toLowerCase().trim();
    const rawTerms = normalizedQuery
        .split(/[\s,.;:!?()[\]{}<>/\\~@#$%^&*+=|`"'_-]+/)
        .filter(Boolean);
    const terms = new Set<string>(rawTerms);

    for (const term of rawTerms) {
        const cjkRegex = /[\u4e00-\u9fa5\u3040-\u30ff\uac00-\ud7af]+/g;
        let match: RegExpExecArray | null;
        while ((match = cjkRegex.exec(term)) !== null) {
            const chars = Array.from(match[0]);
            for (let i = 0; i < chars.length; i++) {
                if (chars.length <= 4) {
                    terms.add(chars[i]);
                }
                if (i + 1 < chars.length) {
                    terms.add(chars[i] + chars[i + 1]);
                }
            }
        }
    }

    const termList = Array.from(terms).filter((t) => t.length > 0);
    if (termList.length === 0) {
        return escapeHtml(text);
    }

    // Sort longer matches first to avoid partial replacements
    termList.sort((a, b) => b.length - a.length);

    const pattern = new RegExp(
        `(${termList.map((t) => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|')})`,
        'gi',
    );

    const parts = text.split(pattern);

    return parts
        .map((part) => {
            if (!part) return '';
            if (pattern.test(part)) {
                return `<mark class="bg-amber-300 text-zinc-950 font-semibold px-0.5 rounded-xs">${escapeHtml(part)}</mark>`;
            }

            return escapeHtml(part);
        })
        .join('');
}

function generateSnippet(content: string, terms: string[]): string {
    if (!content) return '';

    const lower = content.toLowerCase();
    let bestPos = -1;

    for (const term of terms) {
        const pos = lower.indexOf(term.toLowerCase());
        if (pos !== -1 && (bestPos === -1 || pos < bestPos)) {
            bestPos = pos;
        }
    }

    if (bestPos === -1) {
        return content.length > 120 ? content.slice(0, 120) + '...' : content;
    }

    const start = Math.max(0, bestPos - 40);
    const length = Math.min(content.length - start, 120);
    let snippet = content.slice(start, start + length);

    if (start > 0) {
        snippet = '...' + snippet;
    }
    if (start + length < content.length) {
        snippet = snippet + '...';
    }

    return snippet;
}

class SearchEngine {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    private index: any = null;
    private items = new Map<string, NoteSearchItem>();
    private initPromise: Promise<void> | null = null;
    private isInitialized = false;

    public isReady(): boolean {
        return this.isInitialized;
    }

    public async init(): Promise<void> {
        if (this.isInitialized) {
            return;
        }

        if (this.initPromise) {
            return this.initPromise;
        }

        this.initPromise = (async () => {
            try {
                const response = await fetch('/search-index.json', {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error(
                        `Failed to load search index: ${response.status}`,
                    );
                }

                const data: NoteSearchItem[] = await response.json();

                const docIndex = new Document({
                    document: {
                        id: 'id',
                        index: [
                            {
                                field: 'title',
                                tokenize: 'forward',
                                resolution: 9,
                            },
                            {
                                field: 'content',
                                tokenize: 'forward',
                                resolution: 5,
                            },
                        ],
                    },
                    encode: tokenizeSearch,
                });

                this.items.clear();
                for (const item of data) {
                    const id = `${item.category}/${item.slug}`;
                    this.items.set(id, item);
                    docIndex.add({
                        id,
                        title: item.title,
                        content: item.content,
                    });
                }

                this.index = docIndex;
                this.isInitialized = true;
            } catch (err) {
                this.initPromise = null;
                throw err;
            }
        })();

        return this.initPromise;
    }

    public search(query: string): SearchResult[] {
        if (!this.isInitialized || !this.index) {
            return [];
        }

        const trimmed = query.trim();
        if (!trimmed) {
            return [];
        }

        const terms = trimmed.split(/\s+/).filter(Boolean);
        if (terms.length === 0) {
            return [];
        }

        // Search each term across fields
        const termMatches = terms.map((term) => {
            const res = this.index.search(term);
            const docScores = new Map<string, number>();

            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            for (const fieldRes of res as any[]) {
                const isTitle = fieldRes.field === 'title';
                for (const id of fieldRes.result as string[]) {
                    const current = docScores.get(id) ?? 0;
                    docScores.set(id, current + (isTitle ? 100 : 10));
                }
            }

            return docScores;
        });

        // Logical AND intersection across terms
        let candidateIds = Array.from(termMatches[0].keys());
        for (let i = 1; i < termMatches.length; i++) {
            candidateIds = candidateIds.filter((id) => termMatches[i].has(id));
        }

        // If no direct hits and single term contains multi-character CJK, run bigram fallback
        if (candidateIds.length === 0 && terms.length === 1) {
            const cjkRegex = /[\u4e00-\u9fa5\u3040-\u30ff\uac00-\ud7af]+/g;
            let match: RegExpExecArray | null;
            const subQueries: string[] = [];

            while ((match = cjkRegex.exec(terms[0])) !== null) {
                const chars = Array.from(match[0]);
                for (let i = 0; i < chars.length - 1; i++) {
                    subQueries.push(chars[i] + chars[i + 1]);
                }
            }

            if (subQueries.length > 1) {
                const subHits = new Map<string, number>();
                for (const sq of subQueries) {
                    const r = this.index.search(sq);
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    for (const fieldRes of r as any[]) {
                        for (const id of fieldRes.result as string[]) {
                            subHits.set(id, (subHits.get(id) ?? 0) + 1);
                        }
                    }
                }

                const threshold = Math.ceil(subQueries.length / 2);
                for (const [id, count] of subHits.entries()) {
                    if (count >= threshold) {
                        candidateIds.push(id);
                    }
                }
            }
        }

        const normalizedQuery = trimmed.toLowerCase();
        const results: SearchResult[] = [];

        for (const id of candidateIds) {
            const item = this.items.get(id);
            if (!item) continue;

            let score = 0;
            for (const tm of termMatches) {
                score += tm.get(id) ?? 0;
            }

            // Exact title boost
            if (item.title.toLowerCase().includes(normalizedQuery)) {
                score += 200;
            }

            // Exact content boost
            if (item.content.toLowerCase().includes(normalizedQuery)) {
                score += 50;
            }

            const snippet = generateSnippet(item.content, terms);

            results.push({
                category: item.category,
                categoryName: item.categoryName,
                slug: item.slug,
                title: item.title,
                snippet,
                highlightedTitle: highlightMatch(item.title, trimmed),
                highlightedSnippet: highlightMatch(snippet, trimmed),
                score,
            });
        }

        results.sort((a, b) => b.score - a.score);

        return results;
    }
}

export const searchEngine = new SearchEngine();
