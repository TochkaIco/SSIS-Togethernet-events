<x-layouts::app :title="__('Legal')">
    <div class="prose dark:prose-invert">
        {!! Str::markdown(file_get_contents(resource_path('views/terms.md'))) !!}
    </div>
</x-layouts::app>
