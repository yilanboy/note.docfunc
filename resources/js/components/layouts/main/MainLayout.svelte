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

<div class="relative min-h-screen w-full">
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
