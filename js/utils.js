function create(el,classes) {
    let $el = document.createElement(el);
    if (classes && classes.length > 0){
        $el.classList.add(...classes);
    }
    return $el;
}

function removeAllChildren($element)
{

    while ($element.firstChild){
        $element.removeChild( $element.firstChild )
    }

    return true;
}

export {
    create,
    removeAllChildren
}