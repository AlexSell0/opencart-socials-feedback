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
class ControllerExtensionModuleSocialsFeedback extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/socials_feedback');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $languages;

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_socials_feedback', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            foreach ($languages as $language_id => $language) {
                $this->cache->delete("socials_feedback_{$language['language_id']}");
            }

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
            $data['error'] = $this->error;
        } else {
            $data['error_warning'] = '';
            $data['error'] = [];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/socials_feedback', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/socials_feedback', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_socials_feedback_status'])) {
            $data['status'] = $this->request->post['module_socials_feedback_status'];
        } else {
            $data['status'] = $this->config->get('module_socials_feedback_status');
        }


        $this->load->model('tool/image');

        if (isset($this->request->post['module_socials_feedback_links'])) {
            $slides = $this->request->post['module_socials_feedback_links'];
        } elseif (!empty($this->config->get('module_socials_feedback_links'))) {
            $slides = $this->config->get('module_socials_feedback_links');
        } else {
            $slides = [];
        }

        if (isset($this->request->post['module_socials_feedback_color'])) {
            $data['color'] = $this->request->post['module_socials_feedback_color'];
        } elseif (!empty($this->config->get('module_socials_feedback_color'))) {
            $data['color'] = $this->config->get('module_socials_feedback_color');
        } else {
            $data['color'] = '#007bff';
        }

        $data['socials'] = [
            [
                'value' => 'link',
                'name' => 'Ссылка',
            ],
            [
                'value' => 'phone',
                'name' => 'Телефон',
            ],
            [
                'value' => 'email',
                'name' => 'Email',
            ],
            [
                'value' => 'max',
                'name' => 'Max',
            ],
            [
                'value' => 'viber',
                'name' => 'Viber',
            ],
            [
                'value' => 'telegram',
                'name' => 'Telegram',
            ],
            [
                'value' => 'facebook',
                'name' => 'Facebook',
            ],
            [
                'value' => 'instagram',
                'name' => 'Instagram',
            ],
            [
                'value' => 'whatsapp',
                'name' => 'Whatsapp',
            ],
        ];

        $data['module_socials_feedback_links'] = [];

        foreach ($languages as $language) {
            if ($slides && isset($slides[$language['language_id']]) && is_array($slides[$language['language_id']])) {
                foreach ($slides[$language['language_id']] as $slide) {
                    $image = $slide['image'] ?? '';
                    $title = $slide['title'] ?? '';
                    $link = $slide['link'] ?? '';
                    $color = $slide['color'] ?? '';
                    $type = $slide['type'] ?? '';
                    $sort = $slide['sort'] ?? 0;
                    $visible = $slide['visible'] ?? false;

                    if (isset($image) && $image && file_exists(DIR_IMAGE . $image)) {
                        $thumb = $this->model_tool_image->resize($image, 100, 100);
                    } else {
                        $thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
                    }

                    $data['module_socials_feedback_links'][$language['language_id']][] = [
                        'image' => $image,
                        'thumb' => $thumb,
                        'title' => $title,
                        'link' => $link,
                        'color' => $color,
                        'type' => $type,
                        'sort' => $sort,
                        'visible' => $visible,
                    ];
                }
            }
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        $data['default_color'] = '#000000';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/socials_feedback', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/socials_feedback')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['module_socials_feedback_links']) && is_array($this->request->post['module_socials_feedback_links'])) {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();

            foreach ($this->request->post['module_socials_feedback_links'] as $lang_id => $slides) {
                if (!is_array($slides)) continue;
                foreach ($slides as $index => $slide) {
                    $type = isset($slide['type']) ? trim($slide['type']) : '';
                    $link = isset($slide['link']) ? trim($slide['link']) : '';
                    $title = isset($slide['title']) ? trim($slide['title']) : '';
                    $color = isset($slide['color']) ? trim($slide['color']) : '';
                    $image = isset($slide['image']) ? trim($slide['image']) : '';

                    if ($type == 'link') {
                        if (empty($title)) {
                            $this->error['warning'] = $this->language->get('error_title_required');
                            $this->error["error_title_{$lang_id}_{$index}"] = true;
                        }
                        if (empty($link)) {
                            $this->error['warning'] = $this->language->get('error_link_required');
                            $this->error["error_link_{$lang_id}_{$index}"] = true;
                        }
                        if (empty($color)) {
                            $this->error['warning'] = $this->language->get('error_color_required');
                            $this->error["error_color_{$lang_id}_{$index}"] = true;
                        }
                        if (empty($image) || !is_file(DIR_IMAGE . $image)) {
                            $this->error['warning'] = $this->language->get('error_image_required');
                            $this->error["error_image_{$lang_id}_{$index}"] = true;
                        }

                    } else {
                        if (empty($link)) {
                            $this->error['warning'] = $this->language->get('error_link_required');
                            $this->error["error_link_{$lang_id}_{$index}"] = true;
                        }
                    }
                }
            }
        }


        return !$this->error;
    }
}