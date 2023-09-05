import React from "react";
import Recipes from "../containers/Recipes";
import Recipe from "../containers/Recipe";
import {Route, Routes} from "react-router-dom";

const RecipesApp = ({initialProps, appContext}) => {

    return (
        <Routes>
            <Route
                path={"/recipe/:id"}
                element={<Recipe {...initialProps} base={appContext.base} />}
            />
            <Route
                path={"/"}
                exact
                element={<Recipes {...initialProps} base={appContext.base} />}
            />
        </Routes>
    );
};

export default (props) => <RecipesApp {...props} />;
