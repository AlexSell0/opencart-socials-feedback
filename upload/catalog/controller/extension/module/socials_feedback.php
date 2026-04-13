<?php
/**
 * Модуль: Socials Feedback
 * Разработчик: AlexS
 * Email: alexsell72@gmail.com
 * GitHub: https://github.com/AlexSell0/opencart-socials-feedback
 * Telegram: https://t.me/AlexS735
 *
 * @copyright  (c) 2024, AlexS
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package Extension
 * @version 1.0.0
 */
class ControllerExtensionModuleSocialsFeedback extends Controller {
    private $color = [
        'email' => '#ffa627',
        'phone' => '#4CAF50',
        'telegram' => '#26A5E4',
        'max' => '#0d0d0d',
        'instagram' => '#E1306C',
        'facebook' => '#1877F2',
        'whatsapp' => '#25D366',
        'viber' => '#7360F2',
    ];

    private $gradient = [
        'email' => 'linear-gradient(135deg, #FFA627 0%, #FFA627 100%)',
        'phone' => 'linear-gradient(135deg, #4CAF50 0%, #4CAF50 100%)',
        'telegram' => 'linear-gradient(135deg, #0088CC 0%, #008EFB 50%, #0095FF 100%)',
        'max' => '#0d0d0d',
        'instagram' => 'linear-gradient(225deg, #833ab4, #fd1d1d, #fcb045)',
        'facebook' => 'linear-gradient(135deg, #1877F2, #0E5A9C)',
        'whatsapp' => 'linear-gradient(135deg, #25D366 0%, #25D366 100%)',
        'viber' => 'linear-gradient(135deg, #7360F2 0%, #7360F2 100%)'
    ];

    private $default_color = '#000000';


	public function index() {

        if(!$this->config->get('module_socials_feedback_status')){
            return null;
        }

        $this->load->language('extension/module/socials_feedback');
        $lang_id = $this->config->get('config_language_id');

        $cache = $this->cache->get("socials_feedback_{$lang_id}");

        $data['links'] = [];

        if(!$cache || !is_array($cache) || empty($cache['links'] ?? '')){
            $links = $this->config->get('module_socials_feedback_links');
            $data['color'] = $this->config->get('module_socials_feedback_color');

            $this->load->model('tool/image');

            foreach(($links[$lang_id] ?? []) as $link){
                $visible = $link['visible'] ?? null;

                if(!$visible) continue;

                $image = $link['image'] ?? '';
                $type = $link['type'] ?? '';
                $color = $link['color'] ?? '';
                $is_color = $link['is_color'] ?? false;
                $title = $link['title'] ?? '';
                $link_item = $link['link'] ?? '';
                $sort = $link['sort'] ?? '';

                $thumb = $this->getThumb($image, $type);
                $color = $this->getColor($color, $type, $is_color);
                $gradient = $this->getGradient($color, $type, $is_color);
                $title = $this->getName($title, $type);

                if(!$link_item || !$thumb){
                    continue;
                }

                $data['links'][] = [
                    'link' => $this->getLink($link_item, $type),
                    'image' => $image,
                    'thumb' => $thumb,
                    'color' => $color,
                    'gradient' => $gradient,
                    'title' => $title,
                    'sort' => (int) $sort,
                ];
            }

            usort($data['links'], function($a, $b) {
                return $a['sort'] <=> $b['sort']; // Оператор <=> (PHP 7+)
            });

            $cache['links'] = $data['links'];
            $cache['color'] = $data['color'];

            $this->cache->set("socials_feedback_{$lang_id}", $cache);
        }else{
            $data['links'] = $cache['links'];
            $data['color'] = $cache['color'];
        }

        if(!$data['links']){
            return null;
        }

        $this->document->addStyle('catalog/view/javascript/socials_feedback/style.css?v=1.0.0');
        $this->document->addScript('catalog/view/javascript/socials_feedback/script.js?v=1.0.0');

		return $this->load->view('extension/module/socials_feedback', $data);
	}

    private function getLink($link, $type) {
        if (empty($link)) {
            return '';
        }

        $link = trim($link);

        $schemes = ['http://', 'https://', 'mailto:', 'tel:', 'viber://', 'whatsapp://', 'tg://', 'max://'];

        foreach ($schemes as $scheme) {
            if (stripos($link, $scheme) === 0) {
                return $link;
            }
        }

        if ($link[0] === '/' || $link[0] === '#' || $link[0] === '?') {
            return $link;
        }

        if ($type === 'email') {
            return 'mailto:' . $link;
        }

        if ($type === 'phone') {
            return 'tel:' . $link;
        }

        return 'https://' . $link;
    }

    private function getThumb($image, $type) {
        if($image && file_exists(DIR_IMAGE . $image)){
            return $this->model_tool_image->resize($image, 28, 28);
        }else if($type !== 'link' && file_exists(DIR_APPLICATION . "/view/javascript/socials_feedback/sf-social/sf-$type.svg")){
            return "/catalog/view/javascript/socials_feedback/sf-social/sf-$type.svg";
        }

        return null;
    }

    private function getName($title, $type) {
        if($type === 'link' || !empty($title)){
            return $title;
        }


        return $this->language->get("entry_$type");
    }

    private function getColor($color, $type, $is_color) {
        if($is_color && $color){
            return $color;
        } else if($this->color[$type] ?? ''){
            return $this->color[$type];
        }

        return $this->default_color;
    }

    private function getGradient($color, $type, $is_color) {
        if($color && $is_color){
            return $color;
        } else if(isset($this->gradient[$type])){
            return $this->gradient[$type];
        }

        return $this->default_color;
    }
}