<?php

namespace App\Http\Turbo;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class AjaxRedirectResponseFactory
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function createRedirectOrTurbo(Request $request, string $route, string $target = '', array $parameters = []): Response
    {
        if ($request->isXmlHttpRequest() || $request->headers->get('turbo-frame')) {
            return new Response(sprintf(<<<HTML
<turbo-stream action="replace" target="%s"><template>
<script>window.location.reload();</script>
</template></turbo-stream>
HTML, $target), 200, ['Content-Type' => 'text/vnd.turbo-stream.html']);
        }

        return new RedirectResponse($this->urlGenerator->generate($route, $parameters));
    }
}
