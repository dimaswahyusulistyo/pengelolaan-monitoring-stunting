<script>
import { useDisplay } from 'vuetify'
import VerticalNav from '@layouts/components/VerticalNav.vue'

export default defineComponent({
  setup(props, { slots }) {
    const isOverlayNavActive = ref(false)
    const isLayoutOverlayVisible = ref(false)
    const toggleIsOverlayNavActive = useToggle(isOverlayNavActive)
    const route = useRoute()
    const { mdAndDown } = useDisplay()

    syncRef(isOverlayNavActive, isLayoutOverlayVisible)
    
    return () => {
      // 👉 Vertical nav
      const verticalNav = h(VerticalNav, { isOverlayNavActive: isOverlayNavActive.value, toggleIsOverlayNavActive }, {
        'nav-header': () => slots['vertical-nav-header']?.({ toggleIsOverlayNavActive }),
        'before-nav-items': () => slots['before-vertical-nav-items']?.(),
        'default': () => slots['vertical-nav-content']?.(),
        'after-nav-items': () => slots['after-vertical-nav-items']?.(),
      })

      // 👉 Main content (tanpa navbar)
      const main = h('main', { class: 'layout-page-content' }, h('div', { class: 'page-content-container' }, slots.default?.()))

      // 👉 Footer
      const footer = h('footer', { class: 'layout-footer' }, [
        h('div', { class: 'footer-content-container' }, slots.footer?.()),
      ])

      // 👉 Overlay
      const layoutOverlay = h('div', {
        class: ['layout-overlay', { visible: isLayoutOverlayVisible.value }],
        onClick: () => { isLayoutOverlayVisible.value = !isLayoutOverlayVisible.value },
      })

      return h('div', {
        class: [
          'layout-wrapper layout-nav-type-vertical layout-navbar-static layout-footer-static layout-content-width-fluid',
          mdAndDown.value && 'layout-overlay-nav',
          route.meta.layoutWrapperClasses,
        ],
      }, [
        verticalNav,
        h('div', { class: 'layout-content-wrapper' }, [
          // Hapus navbar dari sini
          main,
          footer,
        ]),
        layoutOverlay,
      ])
    }
  },
})
</script>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/placeholders";
@use "@layouts/styles/mixins";

.layout-wrapper.layout-nav-type-vertical {
  block-size: 100%;

  .layout-content-wrapper {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-block-size: 100dvh;
    transition: padding-inline-start 0.2s ease-in-out;
    will-change: padding-inline-start;

    @media screen and (min-width: 1280px) {
      padding-inline-start: variables.$layout-vertical-nav-width;
    }
  }

  // 👉 Footer
  .layout-footer {
    @include mixins.boxed-content;
  }

  // 👉 Layout overlay
  .layout-overlay {
    position: fixed;
    z-index: variables.$layout-overlay-z-index;
    background-color: rgb(0 0 0 / 60%);
    cursor: pointer;
    inset: 0;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease-in-out;
    will-change: opacity;

    &.visible {
      opacity: 1;
      pointer-events: auto;
    }
  }

  // Adjust right column pl when vertical nav is collapsed
  &.layout-vertical-nav-collapsed .layout-content-wrapper {
    @media screen and (min-width: 1280px) {
      padding-inline-start: variables.$layout-vertical-nav-collapsed-width;
    }
  }

  // 👉 Content height fixed
  &.layout-content-height-fixed {
    .layout-content-wrapper {
      max-block-size: 100dvh;
    }

    .layout-page-content {
      display: flex;
      overflow: hidden;

      .page-content-container {
        inline-size: 100%;

        > :first-child {
          max-block-size: 100%;
          overflow-y: auto;
        }
      }
    }
  }
}
</style>