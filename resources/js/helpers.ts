export function back() {
    window.history.back();
}

export function once<T = any, E extends Event = Event>(
    fn: ((this: T, event: E) => unknown) | null | undefined,
): (this: T, event: E) => void {
    return function (this: T, event: E): void {
        if (fn) fn.call(this, event);
        fn = null;
    };
}

export function preventDefault<
    T = any,
    E extends { preventDefault: () => void } = Event,
>(fn: (this: T, event: E) => unknown): (this: T, event: E) => void {
    return function (this: T, event: E): void {
        event.preventDefault();
        fn.call(this, event);
    };
}

export function stopPropagation(fn: (event: Event) => void) {
    return function (this: (event: Event) => void, event: Event) {
        event.stopPropagation();
        fn.call(this, event);
    };
}

export function debounce<T extends (...args: unknown[]) => void>(
    callback: T,
    delay: number,
): (...args: Parameters<T>) => void {
    let timeoutId: ReturnType<typeof setTimeout>;

    return function (this: ThisParameterType<T>, ...args: Parameters<T>): void {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(() => {
            callback.apply(this, args);
        }, delay);
    };
}
