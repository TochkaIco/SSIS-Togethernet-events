<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SitemapController
{
    public function index(): Response
    {
        $routes = Route::getRoutes();
        $urls = [];
        $publicNames = ['home', 'faq', 'legal', 'events', 'event.show'];
        foreach ($routes->getRoutes() as $route) {
            // Only consider GET routes with a name and no required parameters
            if (! in_array('GET', $route->methods())) {
                continue;
            }
            // Exclude routes that have required parameters (contain { in uri)
            if (strpos($route->uri(), '{') !== false) {
                continue;
            }
            // Only include routes with names in the allowed list
            $name = $route->getName();
            if (! in_array($name, $publicNames)) {
                continue;
            }
            $url = URL::to($route->uri());
            $urls[] = $url;
        }

        // Add URLs for each specific event
        foreach (Event::where('display_starts_at', '<', now())->get() as $event) {
            $urls[] = route('event.show', ['event' => $event->id]);
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            $xml .= "  <url><loc>{$url}</loc></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
