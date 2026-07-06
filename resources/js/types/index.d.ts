export interface NoteSummary {
    slug: string;
    title: string;
}

export interface NoteCategory {
    slug: string;
    displayName: string;
    notes: NoteSummary[];
}

export interface PageProps {
    noteTree: NoteCategory[];
}
