<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCookiePolicyRequest;
use App\Models\CookiePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CookiePolicyController extends Controller
{
    /**
     * Mostra il form di edit - crea il record se non esiste
     */
    public function edit(): View
    {
        $policy = CookiePolicy::getOrCreate();

        return view('admin.cookie-policy.edit', [
            'policy' => $policy,
        ]);
    }

    /**
     * Aggiorna il contenuto della cookie policy
     */
    public function update(UpdateCookiePolicyRequest $request): RedirectResponse
    {
        $policy = CookiePolicy::find(1) ?? CookiePolicy::getOrCreate();

        // Sanitizza l'HTML permettendo tag e attributi safe compatibili con TipTap
        $content = html_entity_decode($request->validated()['content']);
        $sanitized = $this->sanitizeHtml($content);

        $policy->updateAndInvalidate(
            $sanitized,
            Auth::id()
        );

        return redirect()->route('cookie-policy.edit')
            ->with('success', __('cookie_policy.updated_successfully'));
    }

    /**
     * Mostra la cookie policy attiva (pubblica)
     */
    public function show(): View
    {
        $policy = CookiePolicy::find(1);

        // La gestione dell'accettazione è fatta lato client in localStorage
        // Questo parametro rimane per compatibilità con la vista
        $requiresAcceptance = ! Auth::check();

        return view('cookie-policy.show', [
            'policy' => $policy,
            'requiresAcceptance' => $requiresAcceptance,
        ]);
    }

    /**
     * Sanitizza l'HTML permettendo solo tag e attributi safe
     */
    private function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'code', 'pre',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li',
            'blockquote', 'hr', 'a', 'img', 'div', 'span',
        ];

        $allowedAttributes = [
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'class', 'style', 'width', 'height'],
            'div' => ['class', 'style'],
            'span' => ['class', 'style'],
            'code' => ['class'],
            'pre' => ['class'],
        ];

        try {
            $dom = new \DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
            libxml_clear_errors();

            $this->removeDisallowedElements($dom, $allowedTags);
            $this->removeDisallowedAttributes($dom, $allowedTags, $allowedAttributes);

            $html = $dom->saveHTML();

            // Rimuovi il wrapper XML che aggiungiamo
            $html = preg_replace('/<\?xml.*?\?>/', '', $html);
            $html = preg_replace('/<(!DOCTYPE|html|body).*?>/i', '', $html);
            $html = preg_replace('/<\/(html|body)>/i', '', $html);

            return trim($html);
        } catch (\Exception $e) {
            // Fallback: return original html (già decodificato)
            return $html;
        }
    }

    /**
     * Rimuove elementi non permessi dal DOM
     */
    private function removeDisallowedElements(\DOMDocument $dom, array $allowedTags): void
    {
        $xpath = new \DOMXPath($dom);
        $allElements = $xpath->query('.//*');

        foreach ($allElements as $element) {
            if (! in_array(strtolower($element->nodeName), $allowedTags)) {
                while ($element->firstChild) {
                    $element->parentNode->insertBefore($element->firstChild, $element);
                }
                $element->parentNode->removeChild($element);
            }
        }
    }

    /**
     * Rimuove attributi non permessi dal DOM
     */
    private function removeDisallowedAttributes(\DOMDocument $dom, array $allowedTags, array $allowedAttributes): void
    {
        $xpath = new \DOMXPath($dom);
        $allElements = $xpath->query('.//*');

        foreach ($allElements as $element) {
            /** @var \DOMElement $element */
            $tagName = strtolower($element->nodeName);

            if (in_array($tagName, $allowedTags)) {
                $allowedAttrs = $allowedAttributes[$tagName] ?? [];

                $attributesToRemove = [];
                foreach ($element->attributes as $attr) {
                    if (! in_array($attr->nodeName, $allowedAttrs)) {
                        $attributesToRemove[] = $attr->nodeName;
                    }
                }

                foreach ($attributesToRemove as $attrName) {
                    $element->removeAttribute($attrName);
                }
            }
        }
    }

    /**
     * Registra l'accettazione della cookie policy (lato client)
     * La gestione è principalmente in localStorage, questa è solo per sincronizzazione
     */
    public function accept(): RedirectResponse
    {
        $redirectTo = session()->pull('cookie_policy_redirect_to', route('dashboard'));

        // Validazione URL sicura - previene open redirect
        if (! str_starts_with($redirectTo, url('/'))) {
            $redirectTo = route('dashboard');
        }

        return redirect($redirectTo);
    }

    /**
     * Rifiuta la cookie policy e sloggazione
     */
    public function decline(): RedirectResponse
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/')->with('message',
            __('cookie_policy.acceptance_required'));
    }

    /**
     * Ritorna le informazioni della cookie policy (JSON API)
     */
    public function getInfo(): \Illuminate\Http\JsonResponse
    {
        $policy = CookiePolicy::find(1);

        if (! $policy) {
            return response()->json([
                'exists' => false,
                'content' => null,
                'updated_at' => null,
            ]);
        }

        return response()->json([
            'exists' => true,
            'has_content' => ! empty($policy->content),
            'updated_at' => $policy->updated_at->toIso8601String(),
            'updated_at_timestamp' => $policy->updated_at->timestamp,
        ]);
    }
}
