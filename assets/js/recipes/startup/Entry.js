import React from "react";
import {BrowserRouter} from "react-router-dom";
import {StaticRouter} from "react-router-dom/server";
import App from "./RecipesApp";

const entry = (initialProps, context) => {
    // We render a different router depending on whether we are rendering server side
    // or client side.
    // Also, for Server side rendering we return an object with:
    // componentHtml (the component)
    // title (the title)
    // other data you may need to render the page
    if (context.serverSide) {
        return () =>
            <StaticRouter
                basename={context.base}
                location={context.location}
                context={{}}
            >
                <App initialProps={initialProps} appContext={context}/>
            </StaticRouter>
    }

    return () =>
        <BrowserRouter basename={context.base}>
            <App initialProps={initialProps} appContext={context}/>
        </BrowserRouter>
}

export default entry;
