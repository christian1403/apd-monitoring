export type Item = {
    id: number;
    name: string;
    description: string | null;
    image: string | null;
    image_url: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};