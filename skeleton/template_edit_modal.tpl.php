{# Instead of creating template for edition, you can render "@PonchoAdmin/edit_modal.html.twig" on controller #}
{% extends "@PonchoAdmin/lib/modal/form.html.twig" %}

{% block modal_title %}
    <h5 class="modal-title">{{ (entity.id ? 'Edit' : 'Add') | trans }}</h5>
{% endblock %}