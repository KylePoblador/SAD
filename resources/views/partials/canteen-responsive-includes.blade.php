<style>
    /* Let inner .coinmeal-canteen-wrap control horizontal padding */
    .header {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Wider, centered column on tablet/desktop (matches CoinMeal Tailwind layout). */
    .coinmeal-canteen-wrap {
        width: 100%;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    @media (min-width: 640px) {
        .coinmeal-canteen-wrap {
            max-width: 36rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }
    }

    @media (min-width: 768px) {
        .coinmeal-canteen-wrap {
            max-width: 42rem;
        }
    }

    @media (min-width: 1024px) {
        .coinmeal-canteen-wrap {
            max-width: 56rem;
        }
    }

    @media (min-width: 1280px) {
        .coinmeal-canteen-wrap {
            max-width: 72rem;
        }
    }

    @media (min-width: 1536px) {
        .coinmeal-canteen-wrap {
            max-width: 80rem;
        }
    }

    .coinmeal-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.25rem;
    }

    @media (max-width: 575.98px) {
        .coinmeal-filter-row {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 0.35rem;
            scrollbar-width: thin;
        }

        .coinmeal-filter-row .filter-btn {
            flex: 0 0 auto;
        }
    }
</style>
