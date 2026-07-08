import { MediaQuery } from "svelte/reactivity";

// Must stay in sync with Tailwind's `lg` breakpoint (1024px). Using matchMedia
// means we share the same breakpoint engine as the `lg:` utility classes.
// The `true` fallback is only used during SSR, where the viewport is unknown.
export const isDesktop = new MediaQuery("(min-width: 1024px)", true);

/**
 * Sidebar visibility. `preference` is the user's explicit choice; `null` means
 * "follow the viewport default" (open on desktop, closed on mobile). Keeping
 * the default in CSS (`lg:` classes keyed on `preference === null`) makes the
 * first paint correct before any JavaScript runs, avoiding FOUC.
 */
class Sidebar {
    preference: boolean | null = $state(null);

    get isOpen(): boolean {
        return this.preference ?? isDesktop.current;
    }

    toggle(): void {
        this.preference = !this.isOpen;
    }

    /** Back to the viewport default. */
    reset(): void {
        this.preference = null;
    }
}

export const sidebar = new Sidebar();
