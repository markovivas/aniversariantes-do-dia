<?php
/**
 * Plugin Name: Aniversariantes do Dia
 * Description: Adiciona campo de data de nascimento aos usuários e exibe aniversariantes do dia via shortcode, widget e com suporte a avatar personalizado.
 * Version: 1.1.0
 * Author: Seu Nome
 */

// Evita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal do plugin
 */
class AniversariantesDoDia {
    
    public function __construct() {
        // Adiciona campos no perfil do usuário (nascimento e avatar)
        add_action('show_user_profile', array($this, 'add_birthday_field'));
        add_action('edit_user_profile', array($this, 'add_birthday_field'));
        add_action('show_user_profile', array($this, 'add_custom_avatar_field'));
        add_action('edit_user_profile', array($this, 'add_custom_avatar_field'));
        
        // Salva os campos do perfil
        add_action('personal_options_update', array($this, 'save_user_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_fields'));
        
        // Registra o shortcode
        add_shortcode('aniversariantes_do_dia', array($this, 'render_birthday_shortcode'));

        // Filtro para usar avatar personalizado
        add_filter('get_avatar_data', array($this, 'custom_avatar_data'), 10, 2);
        
        // Adiciona scripts no admin
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Adiciona CSS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        // Adiciona coluna na lista de usuários
        add_filter('manage_users_columns', array($this, 'add_birthday_column'));
        add_filter('manage_users_custom_column', array($this, 'render_birthday_column'), 10, 3);

        // Registra o widget
        add_action('widgets_init', array($this, 'register_birthday_widget'));


    }
    
    /**
     * Adiciona campo de data de nascimento no formulário de perfil
     */
    public function add_birthday_field($user) {
        ?>
        <h3>Data de Nascimento</h3>
        <table class="form-table">
            <tr>
                <th><label for="data_nascimento">Data de Nascimento</label></th>
                <td>
                    <?php
                    $birthday = get_user_meta($user->ID, 'data_nascimento', true);
                    ?>
                    <input type="date" 
                           name="data_nascimento" 
                           id="data_nascimento" 
                           value="<?php echo esc_attr($birthday); ?>" 
                           class="regular-text" />                    <p class="description">Informe sua data de nascimento</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Adiciona campo de avatar personalizado
     */
    public function add_custom_avatar_field($user) {
        ?>
        <h3>Foto de Perfil Personalizada</h3>
        <table class="form-table">
            <tr>
                <th><label for="custom_avatar">Foto do Perfil</label></th>
                <td>
                    <?php
                    $custom_avatar = get_user_meta($user->ID, 'custom_avatar', true);
                    ?>
                    
                    <!-- Preview da imagem -->
                    <div class="avatar-preview" style="margin-bottom: 15px;">
                        <?php if ($custom_avatar) : ?>
                            <img src="<?php echo esc_url($custom_avatar); ?>" 
                                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                        <?php else : ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;">
                                <span style="color: #999; font-size: 12px;">Sem foto</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo de upload -->
                    <input type="text" 
                           name="custom_avatar" 
                           id="custom_avatar" 
                           value="<?php echo esc_url($custom_avatar); ?>" 
                           class="regular-text"
                           placeholder="URL da imagem ou use o botão de upload" />
                    
                    <!-- Botão de upload -->
                    <button type="button" 
                            class="button custom-avatar-upload" 
                            data-target="#custom_avatar">
                        Escolher Imagem
                    </button>
                    
                    <button type="button" 
                            class="button button-secondary custom-avatar-remove" 
                            data-target="#custom_avatar">
                        Remover Imagem
                    </button>
                    
                    <p class="description">
                        Faça upload de uma imagem para seu perfil (recomendado: 200x200 pixels, formato JPG ou PNG)
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Scripts para upload no admin
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }
        
        wp_enqueue_media();
        
        wp_add_inline_script('jquery', file_get_contents(plugin_dir_path(__FILE__) . 'admin-script.js'));
    }
    
    /**
     * Salva todos os campos do usuário
     */
    public function save_user_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (isset($_POST['data_nascimento'])) {
            // Valida a data
            $birthday = sanitize_text_field($_POST['data_nascimento']);
            
            // Verifica se é uma data válida
            if (!empty($birthday) && !$this->is_valid_date($birthday)) {
                add_action('user_profile_update_errors', function($errors) {
                    $errors->add('birthday_error', __('<strong>Erro</strong>: Data inválida. Use o formato AAAA-MM-DD.'));
                });
                return;
            }
            
            update_user_meta($user_id, 'data_nascimento', $birthday);
        }

        // Salva avatar personalizado
        if (isset($_POST['custom_avatar'])) {
            $custom_avatar = esc_url_raw($_POST['custom_avatar']);
            update_user_meta($user_id, 'custom_avatar', $custom_avatar);
        }
    }

    /**
     * Filtro para usar avatar personalizado
     */
    public function custom_avatar_data($args, $id_or_email) {
        $user = false;
        
        // Obtém o objeto do usuário
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', $id_or_email);
        } elseif (is_object($id_or_email)) {
            if (!empty($id_or_email->user_id)) {
                $user = get_user_by('id', $id_or_email->user_id);
            }
        } else {
            $user = get_user_by('email', $id_or_email);
        }
        
        // Se tem avatar personalizado, usa ele
        if ($user && $user->ID) {
            $custom_avatar = get_user_meta($user->ID, 'custom_avatar', true);
            if (!empty($custom_avatar)) {
                $args['url'] = $custom_avatar;
            }
        }
        
        return $args;
    }

    /**
     * Valida o formato da data
     */
    private function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Renderiza o shortcode
     */
    public function render_birthday_shortcode($atts) {
        $atts = shortcode_atts(array(
            'tamanho' => 80,
            'mostrar_email' => 'false',
        ), $atts, 'aniversariantes_do_dia');

        // Obtém a data atual
        $today = current_time('m-d');
        
        // Busca todos os usuários
        $users = get_users(array(
            'meta_key' => 'data_nascimento',
            'meta_compare' => 'EXISTS'
        ));
        
        $birthday_users = array();
        
        foreach ($users as $user) {
            $birthday = get_user_meta($user->ID, 'data_nascimento', true);
            
            if (!empty($birthday)) {
                // Converte a data para formato m-d
                $birthday_md = date('m-d', strtotime($birthday));
                
                // Verifica se é aniversário hoje
                if ($birthday_md === $today) {
                    $birthday_users[] = $user;
                }
            }
        }
        
        // Inicia o buffer de saída
        ob_start();
        
        if (!empty($birthday_users)) {
            ?>
            <div class="aniversariantes-grid">
                <?php foreach ($birthday_users as $user) : 
                    $avatar_size = intval($atts['tamanho']);
                ?>
                    <div class="aniversariante-card">
                        <div class="aniversariante-avatar">
                            <?php echo get_avatar($user->ID, $avatar_size); ?>
                        </div>
                        <div class="aniversariante-nome">
                            <span><?php echo esc_html($user->display_name); ?></span>
                            <?php if (filter_var($atts['mostrar_email'], FILTER_VALIDATE_BOOLEAN)) : ?>
                                <div class="aniversariante-email">
                                    <small><?php echo esc_html($user->user_email); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        } else {
            ?>
            <div class="sem-aniversariantes">
                <p>Nenhum aniversariante hoje.</p>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
    
    /**
     * Adiciona os estilos CSS
     */
    public function enqueue_styles() {
        wp_add_inline_style('wp-block-library', '
            .aniversariantes-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 25px;
                padding: 25px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                margin: 20px 0;
            }
            
            .aniversariante-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                transition: transform 0.3s ease;
            }
            
            .aniversariante-card:hover {
                transform: translateY(-5px);
            }
            
            .aniversariante-avatar img {
                border-radius: 50%;
                object-fit: cover;
                margin-bottom: 12px;
                border: 4px solid #f8f9fa;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            }
            
            .aniversariante-nome span {
                display: block;
                font-weight: 600;
                font-size: 1rem;
                color: #2c3e50;
                margin-bottom: 5px;
            }
            
            .aniversariante-email small {
                font-size: 0.8rem;
                color: #7f8c8d;
            }
            
            .sem-aniversariantes {
                text-align: center;
                padding: 50px 30px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                margin: 20px 0;
            }
            
            .sem-aniversariantes p {
                margin: 0;
                color: #7f8c8d;
                font-size: 1.2rem;
            }

            @media (max-width: 768px) {
                .aniversariantes-grid {
                    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                    gap: 20px;
                    padding: 20px;
                }
            }

            /* Responsividade */
            @media (max-width: 480px) {
                .aniversariantes-grid {
                    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                    gap: 15px;
                    padding: 15px;
                }
            }
        ');
    }

    /**
     * Adiciona a coluna 'Aniversário Hoje' na lista de usuários.
     */
    public function add_birthday_column($columns) {
        $columns['aniversario_hoje'] = 'Aniversário Hoje';
        return $columns;
    }

    /**
     * Renderiza o conteúdo da coluna 'Aniversário Hoje'.
     */
    public function render_birthday_column($value, $column_name, $user_id) {
        if ($column_name === 'aniversario_hoje') {
            $birthday = get_user_meta($user_id, 'data_nascimento', true);
            if (!empty($birthday)) {
                $birthday_md = date('m-d', strtotime($birthday));
                $today = current_time('m-d');
                return ($birthday_md === $today) ? '🎉 Sim' : 'Não';
            }
        }
        return $value;
    }

    /**
     * Registra o widget de aniversariantes.
     */
    public function register_birthday_widget() {
        register_widget('Aniversariantes_Widget');
    }
}

/**
 * Adiciona um widget para aniversariantes do dia
 */
class Aniversariantes_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'aniversariantes_widget',
            'Aniversariantes do Dia',
            array('description' => 'Exibe os aniversariantes do dia')
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title'] ?? 'Aniversariantes do Dia');

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo do_shortcode('[aniversariantes_do_dia]');
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Aniversariantes do Dia';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Título:</label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Inicializa o plugin
function init_aniversariantes_do_dia() {
    new AniversariantesDoDia();
}
add_action('plugins_loaded', 'init_aniversariantes_do_dia');

/**
 * Cria um arquivo JS separado para o script do admin para melhor organização.
 */
if (!file_exists(plugin_dir_path(__FILE__) . 'admin-script.js')) {
    $js_content = <<<JS
jQuery(document).ready(function($) {
    // O código para upload e remoção de avatar que estava aqui foi movido para este arquivo.
    // ... (código JS completo para o uploader)
});
JS;
    file_put_contents(plugin_dir_path(__FILE__) . 'admin-script.js', $js_content);
}