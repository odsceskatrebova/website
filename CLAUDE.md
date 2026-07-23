# Project Guidelines

## Project Purpose

ODSCT is a Sulu 3 project, copied from the `sulu-init-3` bootstrap (`sulu-init` family). It is a standalone project — project-agnostic improvements can be backported upstream to the bootstrap, but this repo's own project-specific work is not.

**Tech stack:** Sulu 3, Vue + Vite + TypeScript, SCSS, API Platform.

**Webspace:** single webspace, key `odsct`, domain `odsct.local`.

## Code Organization

- All Sulu-specific code goes in `src/Sulu/`
- Shared JS/SCSS/Vue used across the project lives in `assets/common/`; the `odsct` webspace entry point (`assets/odsct/`) opts in by importing from it
- always install npm and composer using their repsective containers



# Code Quality and Rules


## Vue Props Typing

Always type props using the object syntax inside `defineProps()`, not with a generic type parameter. Always assign the result to a destructured variable — even if props are only used in the template:

```ts
// Correct
const props = defineProps({
  foo: { type: String, required: true },
  bar: { type: Number, default: 0 },
})

const {foo, bar } = toRefs(props);
// then use foo.value
// there is no need to destructure foo again everytime to access its properties

// Wrong — do not use generic syntax
const props = defineProps<{ foo: string; bar?: number }>()

// Wrong — do not access props directly without toRefs
const props = defineProps({ foo: { type: String, required: true } })
props.foo // loses reactivity tracking — use toRefs instead
```

## Pinia Store Usage

Always destructure computed and ref variables from a store using `storeToRefs()` — no exceptions. Never access reactive store properties via `store.foo` or `store.foo.value` directly.

```ts
// Correct
const store = useMyStore()
const { foo, bar } = storeToRefs(store)
store.someAction()

// Wrong — do not access store state directly
store.foo
store.foo.value
const { foo, bar } = useMyStore()
```

Actions and non-reactive values are destructured directly from the store, not from `storeToRefs()`.
