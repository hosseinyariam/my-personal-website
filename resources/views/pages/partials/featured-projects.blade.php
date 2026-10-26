{{-- بخش پروژه‌های منتخب صفحه اصلی --}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="featured-projects-heading">
    <div class="container-page">
        <x-ui.section-heading
            :title="$title"
            :href="route('projects.index')"
            id="featured-projects-heading" />

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($projects as $project)
                <x-project-card :project="$project" :status-variant="$project->status->badgeClass()" />
            @endforeach
        </div>
    </div>
</section>
