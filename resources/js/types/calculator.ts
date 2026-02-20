export interface HistoryEntry {
    id: number;
    expression: string;
    result: string;
    created_at: string;
}

export type CalcBtn = {
    label: string;
    action: () => void;
    keyShortcut?: string;
    variant?:
        | 'default'
        | 'destructive'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link';
    class?: string;
    tooltip?: string;
};
