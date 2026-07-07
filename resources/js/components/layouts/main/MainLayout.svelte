<script lang="ts">
  import type { Snippet } from "svelte";
  import Sidebar from "@/components/layouts/main/partials/Sidebar.svelte";
  import Header from "@/components/layouts/main/partials/Header.svelte";
  import { sidebar } from "@/shared/sidebar.svelte";

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();
</script>

<div class="relative min-h-screen w-full bg-zinc-50 text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100 transition-colors duration-300">
  <Header />

  <Sidebar />

  <div
    class={{
      "lg:pl-72": sidebar.isOpen,
      "transition-all duration-300 ease-in-out": true,
    }}
  >
    {@render children?.()}
  </div>
</div>
